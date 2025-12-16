<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Produk') }}
        </h2>
    </x-slot>
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Keranjang</h1>

    @if(count($cart) > 0)
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2">Produk</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Subtotal</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($cart as $id => $item)
            @php $subtotal = $item['price'] * $item['qty']; $total += $subtotal; @endphp
            <tr>
                <td class="p-2">{{ $item['name'] }}</td>
                <td class="p-2">Rp {{ number_format($item['price'],0,',','.') }}</td>
                <td class="p-2">{{ $item['qty'] }}</td>
                <td class="p-2">Rp {{ number_format($subtotal,0,',','.') }}</td>
                <td class="p-2">
                    <form action="{{ route('cart.remove', $id) }}" method="POST">
                        @csrf
                        <button class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="3" class="p-2 text-right">Total</td>
                <td class="p-2">Rp {{ number_format($total,0,',','.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <a href="{{ route('checkout.index') }}" class="bg-green-500 text-white px-4 py-2 rounded mt-4 inline-block">Checkout</a>
    @else
    <p>Keranjang kosong.</p>
    @endif
</div>
</x-app-layout>
