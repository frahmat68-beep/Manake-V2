<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\OrderStatusLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle payment webhook callback notification from Midtrans.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Log incoming payload for audit trail
        Log::info('Midtrans Webhook Received: ', $payload);

        // 1. Validate signature key
        if (!$this->midtransService->validateSignature($payload)) {
            Log::warning('Midtrans Webhook Invalid Signature Key: ', $payload);
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature key.'
            ], 403);
        }

        $orderId = $payload['order_id'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $transactionTime = $payload['transaction_time'] ?? '';

        // 2. Enforce idempotency using event_key
        $eventKey = hash('sha256', $orderId . $transactionStatus . $statusCode . $transactionTime);

        if (PaymentWebhookEvent::where('event_key', $eventKey)->exists()) {
            Log::info("Midtrans Webhook duplicate event skipped for key: {$eventKey}");
            return response()->json([
                'success' => true,
                'message' => 'Duplicate webhook event skipped.'
            ], 200);
        }

        // Save Webhook event log
        PaymentWebhookEvent::create([
            'event_key' => $eventKey,
            'midtrans_order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        // 3. Find order by midtrans_order_id
        $order = Order::where('midtrans_order_id', $orderId)->with('payment')->first();

        if (!$order) {
            Log::error("Midtrans Webhook failed to locate order with midtrans_order_id: {$orderId}");
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        // 4. Map the transaction status
        $mapped = $this->midtransService->mapTransactionStatus($payload);
        $paymentStatus = $mapped['payment_status'];

        // Determine correct rental status update
        $fromStatus = $order->rental_status;
        $toStatus = $fromStatus;

        if ($paymentStatus === Order::PAYMENT_PAID) {
            $toStatus = Order::RENTAL_PAID;
        } elseif ($paymentStatus === Order::PAYMENT_EXPIRED) {
            $toStatus = Order::RENTAL_EXPIRED;
        } elseif ($paymentStatus === Order::PAYMENT_FAILED) {
            $toStatus = Order::RENTAL_CANCELLED;
        }

        $paidAt = $paymentStatus === Order::PAYMENT_PAID ? now() : null;

        // Update Payment details
        if ($order->payment) {
            $order->payment->update([
                'transaction_status' => $mapped['transaction_status'],
                'fraud_status' => $mapped['fraud_status'],
                'payment_type' => $payload['payment_type'] ?? 'unknown',
                'gross_amount' => (int) ($payload['gross_amount'] ?? $order->grand_total),
                'raw_payload' => $payload,
                'status' => $paymentStatus,
                'paid_at' => $paidAt,
            ]);
        }

        // Update Order details
        $order->update([
            'payment_status' => $paymentStatus,
            'rental_status' => $toStatus,
            'paid_at' => $paidAt,
        ]);

        // Write transition log
        if ($fromStatus !== $toStatus) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'actor_type' => 'system',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => 'Status pemesanan diperbarui otomatis melalui callback Midtrans.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully.',
        ], 200);
    }
}
