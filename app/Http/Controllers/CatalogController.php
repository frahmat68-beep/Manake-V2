<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Show the catalog page with search and category filters.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        
        $query = Equipment::with('category');

        // Filter by Search Query
        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by Category Slug
        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $equipments = $query->paginate(12)->withQueryString();

        return view('catalog', compact('categories', 'equipments'));
    }
}
