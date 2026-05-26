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
    public function index(Request $request)
    {
        $summary = $this->cartService->getSummary($request->user());

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($summary);
        }

        return view('cart', compact('summary'));
    }

    /**
     * Add a professional rental item to the cart.
     */
    public function store(StoreCartItemRequest $request)
    {
        $equipment = Equipment::findOrFail($request->validated('equipment_id'));

        try {
            $cartItem = $this->cartService->add(
                $request->user(),
                $equipment,
                $request->validated('rental_start_date'),
                $request->validated('rental_end_date'),
                $request->validated('qty')
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                throw $e;
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alat media sewa berhasil ditambahkan ke keranjang belanja Anda.',
                'data' => $cartItem,
            ], 201);
        }

        return redirect()->route('cart.index')->with('success', 'Alat media sewa berhasil ditambahkan ke keranjang belanja Anda.');
    }

    /**
     * Update quantity of a specific item inside the cart.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        // Enforce user ownership
        if ($cartItem->user_id !== $request->user()->id) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk mengubah item ini.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $updatedItem = $this->cartService->update($cartItem, $request->validated('qty'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                throw $e;
            }
            return redirect()->back()->withErrors($e->errors());
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jumlah alat sewa berhasil diperbarui.',
                'data' => $updatedItem,
            ]);
        }

        return redirect()->back()->with('success', 'Jumlah alat sewa berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== $request->user()->id) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak untuk menghapus item ini.',
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $this->cartService->remove($cartItem);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alat sewa berhasil dihapus dari keranjang belanja Anda.',
            ]);
        }

        return redirect()->back()->with('success', 'Alat sewa berhasil dihapus dari keranjang belanja Anda.');
    }
}
