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
     * Configure Midtrans Snap/CoreApi using config/services.php values.
     * Uses config() instead of env() so config:cache works on Vercel.
     */
    public function configure(): void
    {
        Config::$serverKey    = config('services.midtrans.server_key', '');
        Config::$clientKey    = config('services.midtrans.client_key', '');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized  = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds        = (bool) config('services.midtrans.is_3ds', true);
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
        $itemDetailsTotal = 0;

        foreach ($order->items as $item) {
            $durationDays = $item->duration_days;
            $priceCalculated = (int) ($item->price_per_day * $durationDays);
            $qty = (int) $item->qty;
            
            $itemDetails[] = [
                'id' => (string) $item->equipment_id,
                'price' => $priceCalculated,
                'quantity' => $qty,
                'name' => substr($item->equipment_name . ' (' . $durationDays . ' hari)', 0, 50),
            ];

            $itemDetailsTotal += $priceCalculated * $qty;
        }

        // PPN tax as a separate item if greater than 0
        if ($order->tax_amount > 0) {
            $itemDetails[] = [
                'id' => 'TAX-11',
                'price' => (int) $order->tax_amount,
                'quantity' => 1,
                'name' => 'PPN 11%',
            ];
            $itemDetailsTotal += (int) $order->tax_amount;
        }

        // Additional fee as a separate item if greater than 0
        if ($order->additional_fee > 0) {
            $itemDetails[] = [
                'id' => 'ADDITIONAL-FEE',
                'price' => (int) $order->additional_fee,
                'quantity' => 1,
                'name' => 'Biaya Tambahan',
            ];
            $itemDetailsTotal += (int) $order->additional_fee;
        }

        // Verification check before calling Midtrans API
        if ($itemDetailsTotal !== (int) $order->grand_total) {
            throw new \RuntimeException("Total item Midtrans tidak sesuai dengan grand total order. Item Total: {$itemDetailsTotal}, Order Grand Total: {$order->grand_total}");
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
        $serverKey = config('services.midtrans.server_key', '');

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
        } elseif (in_array($transactionStatus, ['deny', 'cancel', 'failure'])) {
            $paymentStatus = Order::PAYMENT_FAILED;
        } elseif ($transactionStatus === 'expire') {
            $paymentStatus = Order::PAYMENT_EXPIRED;
        } elseif (in_array($transactionStatus, ['refund', 'chargeback', 'partial_refund'])) {
            $paymentStatus = Order::PAYMENT_REFUNDED;
        }

        return [
            'payment_status' => $paymentStatus,
            'transaction_status' => $transactionStatus,
            'fraud_status' => $fraudStatus,
        ];
    }
}
