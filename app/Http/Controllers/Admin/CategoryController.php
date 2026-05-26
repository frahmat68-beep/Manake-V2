<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        // Make sure slug is unique
        if (Category::where('slug', $slug)->exists()) {
            return redirect()->back()->withInput()->withErrors(['slug' => 'Slug ini sudah digunakan.']);
        }

        Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat!');
    }

    /**
     * Show the form for editing the category.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the category.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);

        // Make sure slug is unique except current
        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return redirect()->back()->withInput()->withErrors(['slug' => 'Slug ini sudah digunakan.']);
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the category from storage.
     */
    public function destroy(Category $category)
    {
        // Enforce constraint: prevent delete if it contains equipments
        if ($category->equipments()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Kategori tidak dapat dihapus karena memiliki peralatan terikat.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
