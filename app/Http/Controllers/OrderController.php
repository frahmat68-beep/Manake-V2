<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of the customer's orders.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items'])
            ->latest()
            ->paginate(15);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'orders' => $orders,
            ]);
        }

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order details dashboard.
     */
    public function show(Request $request, Order $order)
    {
        // Gated to owner
        abort_unless($order->user_id === $request->user()->id, 403, 'Anda tidak berhak melihat pesanan ini.');

        $order->load(['items.equipment', 'payment']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'order' => $order,
            ]);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Show HTML invoice screen inside browser.
     */
    public function invoice(Request $request, Order $order)
    {
        // Gated to owner and PAID orders
        abort_unless($order->user_id === $request->user()->id, 403, 'Anda tidak berhak melihat invoice ini.');
        abort_unless($order->isPaid(), 403, 'Invoice hanya tersedia untuk pesanan yang sudah dibayar.');

        $order->load(['items.equipment', 'user.profile']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => $this->invoiceService->renderHtml($order),
            ]);
        }

        return view('invoices.rental_template', compact('order'));
    }

    /**
     * Download PDF Invoice.
     */
    public function downloadInvoice(Request $request, Order $order)
    {
        // Gated to owner and PAID orders
        abort_unless($order->user_id === $request->user()->id, 403, 'Anda tidak berhak mendownload invoice ini.');
        abort_unless($order->isPaid(), 403, 'Unduh invoice hanya tersedia untuk pesanan yang sudah lunas.');

        $order->load(['items.equipment', 'user.profile']);

        return $this->invoiceService->downloadPdf($order);
    }

    /**
     * Refresh payment Snap Token if expired or missing.
     */
    public function refreshPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403, 'Anda tidak berhak memperbarui pembayaran ini.');
        abort_if($order->isPaid(), 400, 'Pesanan sudah lunas.');

        try {
            $midtransService = app(MidtransService::class);
            $snapResult = $midtransService->createSnapToken($order);

            $order->payment->update([
                'snap_token' => $snapResult['snap_token'],
                'snap_redirect_url' => $snapResult['redirect_url'] ?? '',
            ]);

            return redirect()->route('orders.show', $order->id)->with('success', 'Token pembayaran berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order->id)->with('error', 'Gagal memperbarui pembayaran: ' . $e->getMessage());
        }
    }
}
