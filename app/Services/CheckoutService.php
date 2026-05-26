<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Service to orchestrate the checkout pipeline.
 * Computes prices, deposits, penalties, and saves transactions securely.
 */
class CheckoutService
{
    protected $availabilityService;
    protected $cartService;

    public function __construct(
        AvailabilityService $availabilityService,
        CartService $cartService
    ) {
        $this->availabilityService = $availabilityService;
        $this->cartService = $cartService;
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
}
