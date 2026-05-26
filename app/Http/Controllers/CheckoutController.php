<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * Preview checkout details before submitting orders.
     */
    public function index(Request $request)
    {
        try {
            $preview = $this->checkoutService->preview($request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                throw $e;
            }
            // Return to cart with errors
            return redirect()->route('cart.index')->withErrors($e->errors());
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'checkout_preview' => $preview,
            ]);
        }

        // Profile reminder: check if profile is complete (e.g. phone or address is null)
        $profile = $request->user()->profile;
        $profileIncomplete = !$profile || !$profile->phone || !$profile->address || !$profile->identity_number;

        return view('checkout.preview', compact('preview', 'profileIncomplete'));
    }

    /**
     * Submit checkout, create order, obtain Snap Token and clear cart.
     */
    public function store(Request $request)
    {
        try {
            $order = $this->checkoutService->createOrderFromCart($request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                throw $e;
            }
            return redirect()->route('cart.index')->withErrors($e->errors());
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return redirect()->route('cart.index')->with('error', 'Checkout gagal: ' . $e->getMessage());
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat.',
                'order' => $order,
            ], 201);
        }

        return redirect()->route('orders.show', $order->id)->with('success', 'Pesanan berhasil dibuat!');
    }
}
