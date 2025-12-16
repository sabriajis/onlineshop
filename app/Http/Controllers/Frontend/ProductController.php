<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    // List produk yang stok > 0
    public function index()
    {
        $products = Product::where('stock', '>', 0)->get();
        return view('frontend.products.index', compact('products'));
    }

    // Detail produk
    public function show(Product $product)
    {
        return view('frontend.products.show', compact('product'));
    }
}
