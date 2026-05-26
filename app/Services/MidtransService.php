<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;

/**
 * Service to handle Midtrans Payment Gateway interactions.
 * Aligned with sandbox credentials and transaction processing.
 */
class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration using environment variables
        Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-placeholder');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-placeholder');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_IS_3DS', true);
    }

    /**
     * Create Midtrans Snap Transaction and get Snap Token.
     *
     * @param string $orderId
     * @param int $grossAmount
     * @param array $customerDetails
     * @param array $itemDetails
     * @return string|null Snap token
     */
    public function createSnapToken(string $orderId, int $grossAmount, array $customerDetails, array $itemDetails = []): ?string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Generation Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process and verify payment notification webhook from Midtrans.
     *
     * @param array $notificationPayload
     * @return array Normalized payment status response
     */
    public function handleNotification(array $notificationPayload): array
    {
        // TODO: Construct Midtrans\Notification object, verify signatures, and decode statuses.
        // Return normalized array (e.g. status: paid, pending, failed, expired) to let caller update order database.
        return [
            'order_id' => $notificationPayload['order_id'] ?? null,
            'transaction_status' => $notificationPayload['transaction_status'] ?? 'unknown',
            'payment_type' => $notificationPayload['payment_type'] ?? 'unknown',
            'fraud_status' => $notificationPayload['fraud_status'] ?? 'accept',
        ];
    }
}
