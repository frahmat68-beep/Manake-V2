<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Filter by payment_status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by rental_status
        if ($request->filled('rental_status')) {
            $query->where('rental_status', $request->rental_status);
        }

        // Search by order_number or customer email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order details.
     */
    public function show(Order $order)
    {
        $order->load(['user.profile', 'items.equipment', 'payment', 'statusLogs.user']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the rental operational status of an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:waiting_payment,paid,processed,picked_up,returned,damaged,lost,completed,cancelled,expired',
        ]);

        $fromStatus = $order->rental_status;
        $toStatus = $request->status;

        if ($fromStatus !== $toStatus) {
            $updateData = ['rental_status' => $toStatus];

            // If changing to cancelled or expired, update payment status accordingly
            if ($toStatus === Order::RENTAL_CANCELLED) {
                $updateData['payment_status'] = Order::PAYMENT_FAILED;
            } elseif ($toStatus === Order::RENTAL_EXPIRED) {
                $updateData['payment_status'] = Order::PAYMENT_EXPIRED;
            } elseif ($toStatus === Order::RENTAL_PAID) {
                $updateData['payment_status'] = Order::PAYMENT_PAID;
            }

            $order->update($updateData);

            // Log status transition
            OrderStatusLog::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'actor_type' => 'admin',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => 'Status sewa diubah manual oleh Administrator.',
            ]);

            return redirect()->route('admin.orders.show', $order->id)->with('success', 'Status sewa berhasil diperbarui!');
        }

        return redirect()->route('admin.orders.show', $order->id)->with('info', 'Status tidak berubah.');
    }

    /**
     * Add late, damage, or lost fees.
     */
    public function addFee(Request $request, Order $order)
    {
        $request->validate([
            'fee_type' => 'required|in:late,damage,lost,other',
            'amount' => 'required|integer|min:1',
            'note' => 'required|string|max:255',
        ]);

        $amount = (int) $request->amount;
        $feeTypeTranslated = '';

        switch ($request->fee_type) {
            case 'late': $feeTypeTranslated = 'Denda Keterlambatan'; break;
            case 'damage': $feeTypeTranslated = 'Denda Kerusakan'; break;
            case 'lost': $feeTypeTranslated = 'Denda Kehilangan'; break;
            case 'other': $feeTypeTranslated = 'Biaya Tambahan Lain'; break;
        }

        // Add additional fee & compute new grand total
        $newAdditionalFee = $order->additional_fee + $amount;
        $newGrandTotal = $order->total_amount + $newAdditionalFee;

        $order->update([
            'additional_fee' => $newAdditionalFee,
            'grand_total' => $newGrandTotal,
        ]);

        // Write transition log containing metadata
        OrderStatusLog::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'admin',
            'from_status' => $order->rental_status,
            'to_status' => $order->rental_status,
            'note' => "Penambahan biaya {$feeTypeTranslated}: Rp " . number_format($amount, 0, ',', '.') . '. Catatan: ' . $request->note,
            'additional_fee' => $amount,
            'metadata' => [
                'fee_type' => $request->fee_type,
                'note' => $request->note,
            ],
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Biaya tambahan berhasil ditambahkan!');
    }
}
