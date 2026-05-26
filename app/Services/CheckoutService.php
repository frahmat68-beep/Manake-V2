<?php

namespace App\Services;

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
     * Process checkout for the user's active cart.
     *
     * @param int $userId
     * @param array $paymentDetails
     * @return array Order data and payment redirect token
     */
    public function processCheckout(int $userId, array $paymentDetails): array
    {
        // 1. Get cart items.
        // 2. Double-check real-time availability.
        // 3. Compute final costs (Total price, security deposit).
        // 4. Create database Order and OrderItem records.
        // 5. Generate Midtrans Snap transaction token.
        // 6. Clear user cart.

        return [
            'success' => true,
            'order_id' => 'TRX-' . time(),
            'total_amount' => 150000,
            'snap_token' => 'mock-snap-token-123456'
        ];
    }

    /**
     * Compute pricing including security deposits, taxes, and potential promo codes.
     *
     * @param array $items
     * @param string|null $promoCode
     * @return array
     */
    public function calculateFees(array $items, ?string $promoCode = null): array
    {
        // TODO: Query promo rules, multiply duration * item rate, calculate required security deposit.
        return [
            'subtotal' => 100000,
            'deposit' => 50000,
            'discount' => 0,
            'total' => 150000
        ];
    }
}
