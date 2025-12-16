<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}

                    <!-- Tombol navigasi dengan icon -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

                        <a href="{{ route('products.index') }}" 
                           class="flex items-center justify-center gap-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                           <x-heroicon-o-shopping-bag class="w-5 h-5" />
                           Lihat Produk
                        </a>

                        <a href="{{ route('cart.index') }}" 
                           class="flex items-center justify-center gap-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                           <x-heroicon-o-shopping-cart class="w-5 h-5" />
                           Keranjang
                        </a>

                        <a href="{{ route('checkout.index') }}" 
                           class="flex items-center justify-center gap-2 bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                           <x-heroicon-o-credit-card class="w-5 h-5" />
                           Checkout
                        </a>

                        <a href="{{ route('tracking.show', 1) }}" 
                           class="flex items-center justify-center gap-2 bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                           <x-heroicon-o-truck class="w-5 h-5" />
                           Tracking
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
