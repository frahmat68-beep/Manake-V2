<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the homepage with featured items and categories.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        
        $featured = Equipment::with('category')
            ->where('status', Equipment::STATUS_READY)
            ->where('stock', '>', 0)
            ->limit(3)
            ->get();

        return view('welcome', compact('categories', 'featured'));
    }
}
