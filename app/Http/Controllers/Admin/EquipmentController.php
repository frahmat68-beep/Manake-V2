<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment.
     */
    public function index()
    {
        $equipments = Equipment::with('category')->latest()->paginate(10);
        return view('admin.equipments.index', compact('equipments'));
    }

    /**
     * Show the form for creating new equipment.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.equipments.create', compact('categories'));
    }

    /**
     * Store new equipment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:equipments,slug',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'price_per_day' => 'required|integer|min:0',
            'status' => 'required|in:ready,maintenance,unavailable',
            'image_path' => 'nullable|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        if (Equipment::where('slug', $slug)->exists()) {
            return redirect()->back()->withInput()->withErrors(['slug' => 'Slug ini sudah digunakan.']);
        }

        Equipment::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'specifications' => $this->normalizeSpecifications($request->input('specifications')),
            'stock' => $request->stock,
            'price_per_day' => $request->price_per_day,
            'status' => $request->status,
            'image_path' => $request->image_path,
        ]);

        return redirect()->route('admin.equipments.index')->with('success', 'Peralatan berhasil dibuat!');
    }

    /**
     * Show the form for editing equipment.
     */
    public function edit(Equipment $equipment)
    {
        $categories = Category::all();
        return view('admin.equipments.edit', compact('equipment', 'categories'));
    }

    /**
     * Update equipment.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:equipments,slug,' . $equipment->id,
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'price_per_day' => 'required|integer|min:0',
            'status' => 'required|in:ready,maintenance,unavailable',
            'image_path' => 'nullable|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        if (Equipment::where('slug', $slug)->where('id', '!=', $equipment->id)->exists()) {
            return redirect()->back()->withInput()->withErrors(['slug' => 'Slug ini sudah digunakan.']);
        }

        $equipment->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'specifications' => $this->normalizeSpecifications($request->input('specifications')),
            'stock' => $request->stock,
            'price_per_day' => $request->price_per_day,
            'status' => $request->status,
            'image_path' => $request->image_path,
        ]);

        return redirect()->route('admin.equipments.index')->with('success', 'Peralatan berhasil diperbarui!');
    }

    /**
     * Delete equipment.
     */
    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return redirect()->route('admin.equipments.index')->with('success', 'Peralatan berhasil dihapus!');
    }

    /**
     * Normalize specifications input for safe PostgreSQL JSON storage.
     *
     * Accepts either:
     * - Valid JSON string  → decoded and stored as array/object
     * - Plain text string  → stored as {"notes": "plain text"}
     * - Null/blank         → stored as NULL
     */
    private function normalizeSpecifications(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode(trim($value), true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return ['notes' => trim($value)];
    }
}
