<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
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
    public function index(Request $request): JsonResponse
    {
        $preview = $this->checkoutService->preview($request->user());

        return response()->json([
            'success' => true,
            'checkout_preview' => $preview,
        ]);
    }
}
