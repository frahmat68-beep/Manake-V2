<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Equipment;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->equipment = Equipment::factory()->create([
            'stock' => 5,
            'status' => 'ready',
            'price_per_day' => 100000,
        ]);
    }

    /**
     * 1. Checkout store rejects empty cart.
     */
    public function test_checkout_store_rejects_empty_cart()
    {
        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHasErrors(['cart']);
    }

    /**
     * 2. Checkout store creates order from valid cart.
     */
    public function test_checkout_store_creates_order_from_valid_cart()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'qty' => 2,
            'price_per_day' => $this->equipment->price_per_day,
            'rental_start_date' => now()->addDays(2)->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        // Mock Midtrans Token Generation
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->once()->andReturn([
                'snap_token' => 'mocked-snap-token-xyz',
                'redirect_url' => 'https://sandbox.midtrans.com/pay/mocked-xyz',
            ]);
        });

        $response = $this->actingAs($this->user)
            ->post(route('checkout.store'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $response->assertRedirect(route('orders.show', $order->id));
        $response->assertSessionHas('success');
    }

    /**
     * 3. Checkout store clears cart after success.
     */
    public function test_checkout_store_clears_cart_after_success()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'equipment_id' => $this->equipment->id,
            'qty' => 1,
            'price_per_day' => $this->equipment->price_per_day,
            'rental_start_date' => now()->addDays(2)->format('Y-m-d'),
            'rental_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createSnapToken')->andReturn([
                'snap_token' => 'mocked-snap-token-xyz',
                'redirect_url' => 'https://sandbox.midtrans.com/pay/mocked-xyz',
            ]);
        });

        $this->actingAs($this->user)
            ->post(route('checkout.store'));

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * 4. Order detail only accessible by owner.
     */
    public function test_order_detail_only_accessible_by_owner()
    {
        $anotherUser = User::factory()->create();
        
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => 'pending',
            'rental_status' => 'waiting_payment',
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        // Access by another user must return 403 Forbidden
        $response = $this->actingAs($anotherUser)
            ->get(route('orders.show', $order->id));
        
        $response->assertStatus(403);

        // Access by owner must return 200 OK
        $response = $this->actingAs($this->user)
            ->get(route('orders.show', $order->id));
        
        $response->assertStatus(200);
    }

    /**
     * 5. Invoice inaccessible before paid.
     */
    public function test_invoice_inaccessible_before_paid()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('orders.invoice', $order->id));

        $response->assertStatus(403);
    }

    /**
     * 6. Invoice accessible after paid.
     */
    public function test_invoice_accessible_after_paid()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PAID,
            'rental_status' => Order::RENTAL_PAID,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
    }

    /**
     * 7. Invoice PDF route works for paid order.
     */
    public function test_invoice_pdf_route_works_for_paid_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PAID,
            'rental_status' => Order::RENTAL_PAID,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('orders.invoice.download', $order->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * 8. Webhook rejects invalid signature.
     */
    public function test_webhook_rejects_invalid_signature()
    {
        // Configure signature mock to fail validation
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('validateSignature')->once()->andReturn(false);
        });

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id' => 'mid-1234',
            'status_code' => '200',
            'gross_amount' => '222000',
            'signature_key' => 'wrong-signature',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Invalid signature key.']);
    }

    /**
     * 9. Webhook paid updates payment/order to paid.
     */
    public function test_webhook_paid_updates_payment_order_to_paid()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->midtrans_order_id,
            'snap_token' => 'snap-xyz',
            'payment_type' => 'midtrans_snap',
            'transaction_status' => 'pending',
            'status' => 'pending',
            'gross_amount' => 222000,
        ]);

        // Mock validation and mapping
        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('validateSignature')->once()->andReturn(true);
            $mock->shouldReceive('mapTransactionStatus')->once()->andReturn([
                'payment_status' => Order::PAYMENT_PAID,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
            ]);
        });

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id' => 'mid-1234',
            'status_code' => '200',
            'gross_amount' => '222000',
            'transaction_status' => 'settlement',
            'transaction_time' => '2026-05-26 12:00:00',
            'payment_type' => 'bank_transfer',
            'signature_key' => 'valid-signature',
        ]);

        $response->assertStatus(200);
        
        $order->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertEquals(Order::RENTAL_PAID, $order->rental_status);
        $this->assertNotNull($order->paid_at);
        
        $payment->refresh();
        $this->assertEquals(Order::PAYMENT_PAID, $payment->status);
        $this->assertEquals('settlement', $payment->transaction_status);
    }

    /**
     * 10. Duplicate webhook event is idempotent.
     */
    public function test_duplicate_webhook_event_is_idempotent()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $eventKey = hash('sha256', 'mid-1234' . 'settlement' . '200' . '2026-05-26 12:00:00');
        
        PaymentWebhookEvent::create([
            'event_key' => $eventKey,
            'midtrans_order_id' => 'mid-1234',
            'transaction_status' => 'settlement',
            'payload' => [],
            'processed_at' => now(),
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('validateSignature')->once()->andReturn(true);
        });

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id' => 'mid-1234',
            'status_code' => '200',
            'gross_amount' => '222000',
            'transaction_status' => 'settlement',
            'transaction_time' => '2026-05-26 12:00:00',
            'signature_key' => 'valid-signature',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Duplicate webhook event skipped.']);
    }

    /**
     * 11. Expired webhook updates order to expired.
     */
    public function test_expired_webhook_updates_order_to_expired()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('validateSignature')->once()->andReturn(true);
            $mock->shouldReceive('mapTransactionStatus')->once()->andReturn([
                'payment_status' => Order::PAYMENT_EXPIRED,
                'transaction_status' => 'expire',
                'fraud_status' => 'accept',
            ]);
        });

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id' => 'mid-1234',
            'status_code' => '407',
            'gross_amount' => '222000',
            'transaction_status' => 'expire',
            'transaction_time' => '2026-05-26 12:00:00',
            'signature_key' => 'valid-signature',
        ]);

        $response->assertStatus(200);
        
        $order->refresh();
        $this->assertEquals(Order::PAYMENT_EXPIRED, $order->payment_status);
        $this->assertEquals(Order::RENTAL_EXPIRED, $order->rental_status);
    }

    /**
     * 12. Failed webhook updates order/payment failed or cancelled.
     */
    public function test_failed_webhook_updates_order_payment_failed_or_cancelled()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'MNK-1234',
            'rental_start_date' => now(),
            'rental_end_date' => now()->addDays(1),
            'duration_days' => 2,
            'subtotal' => 200000,
            'tax_rate' => 11.00,
            'tax_amount' => 22000,
            'total_amount' => 222000,
            'grand_total' => 222000,
            'payment_status' => Order::PAYMENT_PENDING,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
            'midtrans_order_id' => 'mid-1234',
            'expired_at' => now()->addHours(24),
        ]);

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('validateSignature')->once()->andReturn(true);
            $mock->shouldReceive('mapTransactionStatus')->once()->andReturn([
                'payment_status' => Order::PAYMENT_FAILED,
                'transaction_status' => 'deny',
                'fraud_status' => 'accept',
            ]);
        });

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id' => 'mid-1234',
            'status_code' => '406',
            'gross_amount' => '222000',
            'transaction_status' => 'deny',
            'transaction_time' => '2026-05-26 12:00:00',
            'signature_key' => 'valid-signature',
        ]);

        $response->assertStatus(200);
        
        $order->refresh();
        $this->assertEquals(Order::PAYMENT_FAILED, $order->payment_status);
        $this->assertEquals(Order::RENTAL_CANCELLED, $order->rental_status);
    }
}
