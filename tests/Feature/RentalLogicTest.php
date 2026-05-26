<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\CartService;
use App\Services\CheckoutService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RentalLogicTest extends TestCase
{
    use RefreshDatabase;

    protected $availabilityService;
    protected $cartService;
    protected $checkoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availabilityService = $this->app->make(AvailabilityService::class);
        $this->cartService = $this->app->make(CartService::class);
        $this->checkoutService = $this->app->make(CheckoutService::class);
    }

    /**
     * Test available equipment returns available summary.
     */
    public function test_available_equipment_returns_available_summary(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
        ]);

        $summary = $this->availabilityService->getAvailabilitySummary($equipment, '2026-06-01', '2026-06-05', 2);

        $this->assertTrue($summary['ok']);
        $this->assertEquals('available', $summary['status']);
        $this->assertEquals(5, $summary['available_units']);
        $this->assertEquals(0, $summary['reserved_units']);
    }

    /**
     * Test maintenance equipment returns not available.
     */
    public function test_maintenance_equipment_returns_not_available(): void
    {
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_MAINTENANCE,
            'stock' => 5,
        ]);

        $summary = $this->availabilityService->getAvailabilitySummary($equipment, '2026-06-01', '2026-06-05', 1);

        $this->assertFalse($summary['ok']);
        $this->assertEquals('not_available', $summary['status']);
        $this->assertEquals(0, $summary['available_units']);
    }

    /**
     * Test overlapping active (paid) order blocks availability.
     */
    public function test_overlapping_paid_order_blocks_availability(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 2,
        ]);

        // Create an overlapping order: Renting June 2 to June 3
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TRX-101',
            'rental_start_date' => '2026-06-02',
            'rental_end_date' => '2026-06-03',
            'duration_days' => 2,
            'payment_status' => Order::PAYMENT_PAID,
            'rental_status' => Order::RENTAL_PAID,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'equipment_slug' => $equipment->slug,
            'qty' => 2, // Consumes all stock
            'price_per_day' => $equipment->price_per_day,
            'item_subtotal' => $equipment->price_per_day * 2 * 2,
        ]);

        // Request sewa: June 1 to June 4 (overlaps with June 2-3)
        $summary = $this->availabilityService->getAvailabilitySummary($equipment, '2026-06-01', '2026-06-04', 1);

        $this->assertFalse($summary['ok']);
        $this->assertEquals(0, $summary['available_units']);
        $this->assertEquals(2, $summary['reserved_units']);
    }

    /**
     * Test overlapping inactive (failed) order does not block availability.
     */
    public function test_overlapping_failed_order_does_not_block_availability(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 2,
        ]);

        // Order has failed
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TRX-102',
            'rental_start_date' => '2026-06-02',
            'rental_end_date' => '2026-06-03',
            'duration_days' => 2,
            'payment_status' => Order::PAYMENT_FAILED,
            'rental_status' => Order::RENTAL_WAITING_PAYMENT,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'equipment_slug' => $equipment->slug,
            'qty' => 2,
            'price_per_day' => $equipment->price_per_day,
            'item_subtotal' => $equipment->price_per_day * 2 * 2,
        ]);

        // Check availability: should ignore the failed order
        $summary = $this->availabilityService->getAvailabilitySummary($equipment, '2026-06-01', '2026-06-04', 1);

        $this->assertTrue($summary['ok']);
        $this->assertEquals(2, $summary['available_units']);
        $this->assertEquals(0, $summary['reserved_units']);
    }

    /**
     * Test operational buffer blocks adjacent rentals.
     */
    public function test_buffer_day_blocks_adjacent_rental(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 1,
        ]);

        // Active Order blocks June 2 to June 3
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TRX-103',
            'rental_start_date' => '2026-06-02',
            'rental_end_date' => '2026-06-03',
            'duration_days' => 2,
            'payment_status' => Order::PAYMENT_PAID,
            'rental_status' => Order::RENTAL_PAID,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'equipment_slug' => $equipment->slug,
            'qty' => 1,
            'price_per_day' => $equipment->price_per_day,
            'item_subtotal' => $equipment->price_per_day * 2 * 1,
        ]);

        // Test requesting adjacent rental: June 4 to June 5.
        // Requested start = June 4. Buffer start = June 3.
        // Since buffer start (June 3) overlaps with the order end date (June 3), it MUST block!
        $summary = $this->availabilityService->getAvailabilitySummary($equipment, '2026-06-04', '2026-06-05', 1);

        $this->assertFalse($summary['ok']);
        $this->assertEquals(0, $summary['available_units']);
        $this->assertEquals(1, $summary['reserved_units']);
    }

    /**
     * Test cart add works for available equipment.
     */
    public function test_cart_add_works_for_available_equipment(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 3,
        ]);

        $cartItem = $this->cartService->add($user, $equipment, '2026-06-01', '2026-06-02', 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'equipment_id' => $equipment->id,
            'qty' => 2,
        ]);
        $this->assertEquals(2, $cartItem->qty);
    }

    /**
     * Test cart add rejects unavailable date.
     */
    public function test_cart_add_rejects_unavailable_date(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 1,
        ]);

        // Block stock with active order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TRX-104',
            'rental_start_date' => '2026-06-02',
            'rental_end_date' => '2026-06-03',
            'duration_days' => 2,
            'payment_status' => Order::PAYMENT_PAID,
            'rental_status' => Order::RENTAL_PAID,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name,
            'equipment_slug' => $equipment->slug,
            'qty' => 1,
            'price_per_day' => $equipment->price_per_day,
            'item_subtotal' => $equipment->price_per_day * 2,
        ]);

        // Attempting to add the blocked item during the overlapping window should fail
        $this->expectException(ValidationException::class);
        $this->cartService->add($user, $equipment, '2026-06-01', '2026-06-04', 1);
    }

    /**
     * Test cart update rechecks availability.
     */
    public function test_cart_update_rechecks_availability(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 2,
        ]);

        $cartItem = $this->cartService->add($user, $equipment, '2026-06-01', '2026-06-02', 1);

        // Update to 2 units should pass
        $this->cartService->update($cartItem, 2);
        $this->assertEquals(2, $cartItem->fresh()->qty);

        // Update to 3 units (exceeds stock of 2) should throw ValidationException
        $this->expectException(ValidationException::class);
        $this->cartService->update($cartItem, 3);
    }

    /**
     * Test cart summary calculates subtotal, PPN 11%, and total.
     */
    public function test_cart_summary_calculates_subtotal_and_ppn_eleven_percent(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
            'price_per_day' => 100000, // Rp 100,000 / day
        ]);

        // Renting 2 days (June 1 - June 2), qty 2.
        // Subtotal should be: 2 days * Rp 100,000 * 2 = Rp 400,000.
        $this->cartService->add($user, $equipment, '2026-06-01', '2026-06-02', 2);

        $summary = $this->cartService->getSummary($user);

        $this->assertEquals(400000, $summary['subtotal']);
        $this->assertEquals(11.00, $summary['tax_rate']);
        $this->assertEquals(44000, $summary['tax_amount']); // 11% of Rp 400,000 = Rp 44,000
        $this->assertEquals(444000, $summary['total_amount']); // Rp 444,000
    }

    /**
     * Test checkout preview rejects empty cart.
     */
    public function test_checkout_preview_rejects_empty_cart(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);
        $this->checkoutService->preview($user);
    }

    /**
     * Test checkout preview works with valid cart.
     */
    public function test_checkout_preview_works_with_valid_cart(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'status' => Equipment::STATUS_READY,
            'stock' => 5,
            'price_per_day' => 100000,
        ]);

        $this->cartService->add($user, $equipment, '2026-06-01', '2026-06-02', 1);

        $preview = $this->checkoutService->preview($user);

        $this->assertEquals(200000, $preview['subtotal']);
        $this->assertEquals(22000, $preview['tax_amount']);
        $this->assertEquals(222000, $preview['total_amount']);
    }
}
