<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Equipment;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the user's cart items and cost summary.
     */
    public function index(Request $request): JsonResponse
    {
        $summary = $this->cartService->getSummary($request->user());

        return response()->json($summary);
    }

    /**
     * Add a professional rental item to the cart.
     */
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        $equipment = Equipment::findOrFail($request->validated('equipment_id'));

        $cartItem = $this->cartService->add(
            $request->user(),
            $equipment,
            $request->validated('rental_start_date'),
            $request->validated('rental_end_date'),
            $request->validated('qty')
        );

        return response()->json([
            'success' => true,
            'message' => 'Alat media sewa berhasil ditambahkan ke keranjang belanja Anda.',
            'data' => $cartItem,
        ], 201);
    }

    /**
     * Update quantity of a specific item inside the cart.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        // Enforce user ownership
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk mengubah item ini.',
            ], 403);
        }

        $updatedItem = $this->cartService->update($cartItem, $request->validated('qty'));

        return response()->json([
            'success' => true,
            'message' => 'Jumlah alat sewa berhasil diperbarui.',
            'data' => $updatedItem,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menghapus item ini.',
            ], 403);
        }

        $this->cartService->remove($cartItem);

        return response()->json([
            'success' => true,
            'message' => 'Alat sewa berhasil dihapus dari keranjang belanja Anda.',
        ]);
    }
}
