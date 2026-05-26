<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show the detailed view for a single equipment item.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load('category', 'images');
        
        return view('product.show', compact('equipment'));
    }
}
