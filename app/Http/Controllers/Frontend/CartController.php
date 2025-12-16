<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Tampilkan isi cart
    public function index(Request $request)
    {
        $cart = session()->get('cart', []);
        return view('frontend.cart.index', compact('cart'));
    }

    // Tambah produk ke cart
    public function add(Product $product, Request $request)
    {
        $cart = session()->get('cart', []);
        $qty = $request->input('qty', 1);

        if(isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $qty;
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => $qty,
                'weight' => $product->weight
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produk ditambahkan ke cart');
    }

    // Hapus produk dari cart
    public function remove($item, Request $request)
    {
        $cart = session()->get('cart', []);
        unset($cart[$item]);
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produk dihapus dari cart');
    }
}
