@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex gap-6">
        <div class="w-1/2">
            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="w-full h-auto object-cover rounded">
        </div>
        <div class="w-1/2">
            <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
            <p class="text-gray-700 my-2">Rp {{ number_format($product->price,0,',','.') }}</p>
            <p class="text-gray-500 mb-2">Stok: {{ $product->stock }}</p>
            <p class="mb-4">{{ $product->description }}</p>

            @if($product->stock > 0)
            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                @csrf
                <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="border rounded p-1 w-16">
                <button class="bg-blue-500 text-white px-3 py-1 rounded">Beli</button>
            </form>
            @else
            <button disabled class="bg-gray-400 text-white px-3 py-1 rounded">Stok Habis</button>
            @endif
        </div>
    </div>
</div>
@endsection
