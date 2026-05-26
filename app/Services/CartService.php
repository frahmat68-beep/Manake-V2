<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Equipment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Service to manage user rental carts.
 * Tracks selected equipment and validates constraints before checkout.
 */
class CartService
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get all active cart items for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getItems(User $user)
    {
        return CartItem::with('equipment')->where('user_id', $user->id)->get();
    }

    /**
     * Get total quantity of items in user's cart.
     *
     * @param User $user
     * @return int
     */
    public function count(User $user): int
    {
        return (int) CartItem::where('user_id', $user->id)->sum('qty');
    }

    /**
     * Add an item to the rental cart.
     * Re-checks real-time availability including buffers.
     *
     * @param User $user
     * @param Equipment $equipment
     * @param string $startDate
     * @param string $endDate
     * @param int $qty
     * @return CartItem
     * @throws ValidationException
     */
    public function add(User $user, Equipment $equipment, string $startDate, string $endDate, int $qty): CartItem
    {
        // 1. Normalize and check dates
        [$start, $end] = $this->availabilityService->normalizeDateRange($startDate, $endDate);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah sewa alat tidak boleh kurang dari 1.',
            ]);
        }

        // Check if there is an existing cart item with the EXACT same dates and equipment
        $existing = CartItem::where('user_id', $user->id)
            ->where('equipment_id', $equipment->id)
            ->where('rental_start_date', $start->toDateString())
            ->where('rental_end_date', $end->toDateString())
            ->first();

        $totalQty = $qty;
        if ($existing) {
            $totalQty += $existing->qty;
        }

        // 2. Validate availability against accumulated quantity
        $this->availabilityService->assertAvailable($equipment, $start, $end, $totalQty);

        // 3. Persist
        return CartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'rental_start_date' => $start->toDateString(),
                'rental_end_date' => $end->toDateString(),
            ],
            [
                'qty' => $totalQty,
                'price_per_day' => $equipment->price_per_day,
            ]
        );
    }

    /**
     * Update quantity of a specific cart item.
     * Re-checks availability.
     *
     * @param CartItem $cartItem
     * @param int $qty
     * @return CartItem
     * @throws ValidationException
     */
    public function update(CartItem $cartItem, int $qty): CartItem
    {
        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah sewa alat tidak boleh kurang dari 1.',
            ]);
        }

        // Load equipment to run assertions
        $equipment = $cartItem->equipment;

        // Re-assert availability for the updated quantity
        $this->availabilityService->assertAvailable(
            $equipment,
            $cartItem->rental_start_date,
            $cartItem->rental_end_date,
            $qty
        );

        $cartItem->update(['qty' => $qty]);

        return $cartItem;
    }

    /**
     * Remove an item from the rental cart.
     *
     * @param CartItem $cartItem
     * @return void
     */
    public function remove(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    /**
     * Clear the user's cart.
     *
     * @param User $user
     * @return void
     */
    public function clear(User $user): void
    {
        CartItem::where('user_id', $user->id)->delete();
    }

    /**
     * Get summary metrics for the user's active cart.
     * Tax/PPN is exactly 11%.
     *
     * @param User $user
     * @return array
     */
    public function getSummary(User $user): array
    {
        $items = $this->getItems($user);
        $subtotal = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            // Rental dates are inclusive
            $duration = Order::calculateDurationDays($item->rental_start_date, $item->rental_end_date);
            $itemSubtotal = $duration * $item->price_per_day * $item->qty;
            
            // Append virtual properties to aid blade/preview rendering
            $item->duration_days = $duration;
            $item->item_subtotal = $itemSubtotal;

            $subtotal += $itemSubtotal;
            $totalItems += $item->qty;
        }

        $taxRate = 11.00;
        // PPN 11%
        $taxAmount = (int) round(($subtotal * $taxRate) / 100);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'total_items' => $totalItems,
        ];
    }
}
