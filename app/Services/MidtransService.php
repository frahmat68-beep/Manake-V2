<?php

namespace App\Services;

use App\Models\Order;
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
        $this->configure();
    }

    /**
     * Configure Midtrans Snap/CoreApi using env/config values.
     */
    public function configure(): void
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-placeholder');
        Config::$clientKey = env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-placeholder');
        Config::$isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        Config::$isSanitized = filter_var(env('MIDTRANS_IS_SANITIZED', true), FILTER_VALIDATE_BOOLEAN);
        Config::$is3ds = filter_var(env('MIDTRANS_IS_3DS', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Create Midtrans Snap Transaction and get Snap Token and redirect URL.
     *
     * @param Order $order
     * @return array
     * @throws \Exception
     */
    public function createSnapToken(Order $order): array
    {
        $this->configure();

        // Build item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => (string) $item->equipment_id,
                'price' => (int) $item->price_per_day,
                'quantity' => (int) $item->qty,
                'name' => substr($item->equipment->name . ' (' . $item->duration_days . ' hari)', 0, 50),
            ];
        }

        // PPN tax as a separate item if greater than 0
        if ($order->tax_amount > 0) {
            $itemDetails[] = [
                'id' => 'TAX-11',
                'price' => (int) $order->tax_amount,
                'quantity' => 1,
                'name' => 'PPN (11%)',
            ];
        }

        // Customer details
        $user = $order->user;
        $profile = $user->profile;
        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $profile ? ($profile->phone ?? '') : '',
        ];

        $params = [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            $response = Snap::createTransaction($params);
            
            return [
                'snap_token' => $response->token,
                'redirect_url' => $response->redirect_url,
            ];
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Generation Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate webhook signature.
     *
     * @param array $payload
     * @return bool
     */
    public function validateSignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-placeholder');

        $input = $orderId . $statusCode . $grossAmount . $serverKey;
        $signature = hash('sha512', $input);

        return hash_equals($signature, $payload['signature_key'] ?? '');
    }

    /**
     * Map transaction status from Midtrans webhook into our local status representation.
     *
     * @param array $payload
     * @return array
     */
    public function mapTransactionStatus(array $payload): array
    {
        $transactionStatus = $payload['transaction_status'] ?? 'unknown';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $paymentStatus = Order::PAYMENT_PENDING;

        if ($transactionStatus === 'capture') {
            if (($payload['payment_type'] ?? '') === 'credit_card') {
                if ($fraudStatus === 'challenge') {
                    $paymentStatus = Order::PAYMENT_PENDING;
                } else {
                    $paymentStatus = Order::PAYMENT_PAID;
                }
            } else {
                $paymentStatus = Order::PAYMENT_PAID;
            }
        } elseif ($transactionStatus === 'settlement') {
            $paymentStatus = Order::PAYMENT_PAID;
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = Order::PAYMENT_PENDING;
        } elseif (in_array($transactionStatus, ['deny', 'cancel'])) {
            $paymentStatus = Order::PAYMENT_FAILED;
        } elseif ($transactionStatus === 'expire') {
            $paymentStatus = Order::PAYMENT_EXPIRED;
        } elseif ($transactionStatus === 'refund') {
            $paymentStatus = Order::PAYMENT_REFUNDED;
        }

        return [
            'payment_status' => $paymentStatus,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
        ];
    }
}
