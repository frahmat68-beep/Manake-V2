<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\CartItem;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Service to orchestrate the checkout pipeline.
 * Computes prices, deposits, penalties, and saves transactions securely.
 */
class CheckoutService
{
    protected $availabilityService;
    protected $cartService;
    protected $midtransService;

    public function __construct(
        AvailabilityService $availabilityService,
        CartService $cartService,
        MidtransService $midtransService
    ) {
        $this->availabilityService = $availabilityService;
        $this->cartService = $cartService;
        $this->midtransService = $midtransService;
    }

    /**
     * Preview checkout details for the user's active cart.
     * Throws validation exception if the cart is empty or if any item is no longer available.
     *
     * @param User $user
     * @return array Consolidated preview metrics
     * @throws ValidationException
     */
    public function preview(User $user): array
    {
        $summary = $this->cartService->getSummary($user);

        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang belanja Anda kosong. Silakan pilih alat media terlebih dahulu.',
            ]);
        }

        // Re-check real-time availability for every single item in the cart
        foreach ($summary['items'] as $item) {
            $this->availabilityService->assertAvailable(
                $item->equipment,
                $item->rental_start_date,
                $item->rental_end_date,
                $item->qty
            );
        }

        return $summary;
    }

    /**
     * Process checkout conversion to a physical Order with Midtrans Snapshot token.
     *
     * @param User $user
     * @return Order
     * @throws \Exception
     */
    public function createOrderFromCart(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            // Lock and retrieve user cart items
            $cartItems = CartItem::where('user_id', $user->id)
                ->with('equipment')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang belanja Anda kosong. Silakan pilih alat media terlebih dahulu.',
                ]);
            }

            $earliestStart = null;
            $latestEnd = null;
            $subtotal = 0;

            // Re-verify availability and dates for every cart item
            foreach ($cartItems as $item) {
                $this->availabilityService->assertAvailable(
                    $item->equipment,
                    $item->rental_start_date,
                    $item->rental_end_date,
                    $item->qty
                );

                $duration = Order::calculateDurationDays($item->rental_start_date, $item->rental_end_date);
                $itemSubtotal = $item->qty * $item->equipment->price_per_day * $duration;
                $subtotal += $itemSubtotal;

                $start = Carbon::parse($item->rental_start_date);
                $end = Carbon::parse($item->rental_end_date);

                if (is_null($earliestStart) || $start->lt($earliestStart)) {
                    $earliestStart = $start;
                }
                if (is_null($latestEnd) || $end->gt($latestEnd)) {
                    $latestEnd = $end;
                }
            }

            // Calculation metrics
            $durationDays = Order::calculateDurationDays($earliestStart, $latestEnd);
            $taxRate = 11.00;
            $taxAmount = (int) round($subtotal * 0.11);
            $totalAmount = $subtotal + $taxAmount;
            $additionalFee = 0;
            $grandTotal = $totalAmount;

            // Generate unique numbers
            $orderNumber = 'MNK-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $midtransOrderId = 'MNK-MIDTRANS-' . Str::uuid();

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'rental_start_date' => $earliestStart,
                'rental_end_date' => $latestEnd,
                'duration_days' => $durationDays,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'additional_fee' => $additionalFee,
                'grand_total' => $grandTotal,
                'payment_status' => Order::PAYMENT_PENDING,
                'rental_status' => Order::RENTAL_WAITING_PAYMENT,
                'midtrans_order_id' => $midtransOrderId,
                'expired_at' => Carbon::now()->addHours(24),
            ]);

            // Save Snapshot Order Items
            foreach ($cartItems as $item) {
                $duration = Order::calculateDurationDays($item->rental_start_date, $item->rental_end_date);
                $itemSubtotal = $item->qty * $item->equipment->price_per_day * $duration;

                OrderItem::create([
                    'order_id' => $order->id,
                    'equipment_id' => $item->equipment_id,
                    'equipment_name' => $item->equipment->name,
                    'equipment_slug' => $item->equipment->slug,
                    'qty' => $item->qty,
                    'price_per_day' => $item->equipment->price_per_day,
                    'item_subtotal' => $itemSubtotal,
                    'rental_start_date' => $item->rental_start_date,
                    'rental_end_date' => $item->rental_end_date,
                ]);
            }

            // Create Order Status Log initial record
            OrderStatusLog::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'actor_type' => 'customer',
                'from_status' => '',
                'to_status' => Order::RENTAL_WAITING_PAYMENT,
                'note' => 'Pemesanan dibuat, menunggu proses pembayaran.',
            ]);

            // Create Payment record placeholder
            $payment = Payment::create([
                'order_id' => $order->id,
                'midtrans_order_id' => $midtransOrderId,
                'snap_token' => '',
                'snap_redirect_url' => '',
                'payment_type' => 'midtrans_snap',
                'transaction_status' => 'pending',
                'fraud_status' => 'accept',
                'status' => 'pending',
                'gross_amount' => $grandTotal,
            ]);

            // Request Token from Midtrans (throws on error and automatically rolls back db transaction)
            $snapResult = $this->midtransService->createSnapToken($order);

            $payment->update([
                'snap_token' => $snapResult['snap_token'],
                'snap_redirect_url' => $snapResult['redirect_url'] ?? '',
            ]);

            // Purge User CartItems
            CartItem::where('user_id', $user->id)->delete();

            return $order->load(['items.equipment', 'payment']);
        });
    }
}
