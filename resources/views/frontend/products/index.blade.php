<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($products as $product)
                    <div class="border p-4 rounded shadow">
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover mb-2">
                        <h2 class="font-bold text-lg">{{ $product->name }}</h2>
                        <p class="text-gray-700">Rp {{ number_format($product->price,0,',','.') }}</p>
                        <p class="text-gray-500">Stok: {{ $product->stock }}</p>

                        @if($product->stock > 0)
                        <form action="{{ route('cart.add', $product->id) }}" method="POST" class="mt-2">
                            @csrf
                            <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="border rounded p-1 w-16">
                            <button class="bg-blue-500 text-white px-3 py-1 rounded">Beli</button>
                        </form>
                        @else
                        <button disabled class="bg-gray-400 text-white px-3 py-1 rounded mt-2">Stok Habis</button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
