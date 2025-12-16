<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Checkout') }}</h2>
    </x-slot>

    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Checkout</h1>

        @php
            $cart = session()->get('cart', []);
            $total = collect($cart)->sum(fn($c) => $c['price'] * $c['qty']);
        @endphp

        @if(count($cart) > 0)
            <table class="w-full border mb-4">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left">Produk</th>
                        <th class="p-2">Qty</th>
                        <th class="p-2">Harga</th>
                        <th class="p-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $item)
                    <tr>
                        <td class="p-2">{{ $item['name'] }}</td>
                        <td class="p-2 text-center">{{ $item['qty'] }}</td>
                        <td class="p-2 text-right">Rp {{ number_format($item['price'],0,',','.') }}</td>
                        <td class="p-2 text-right">Rp {{ number_format($item['price'] * $item['qty'],0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="mb-4 font-bold text-right">Total Produk: Rp {{ number_format($total,0,',','.') }}</p>

            <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
                @csrf

                {{-- Destination search --}}
                <div class="mb-4">
                    <label class="block mb-1 font-medium">Cari Tujuan (Kecamatan/Kota/Provinsi):</label>
                    <input type="text" id="destination_search" class="border rounded p-2 w-full" placeholder="Ketik nama kecamatan atau kota..." autocomplete="off">
                    <ul id="destination_list" class="border rounded mt-1 max-h-40 overflow-y-auto bg-white hidden"></ul>

                    <input type="hidden" name="destination[id]" id="destination_id">
                    <input type="hidden" name="destination[province_name]" id="destination_province">
                    <input type="hidden" name="destination[city_name]" id="destination_city">
                    <input type="hidden" name="destination[district_name]" id="destination_district">
                    <input type="hidden" name="destination[zip_code]" id="destination_zip">
                </div>

                {{-- Kurir --}}
                <div class="mb-4" id="shipping-options">
                    <label class="block mb-1 font-medium">Pilih Kurir:</label>
                    <p class="text-gray-500">Pilih tujuan terlebih dahulu</p>
                </div>
                <input type="hidden" name="courier_option" id="courier_option">

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded w-full">
                    Bayar
                </button>
            </form>

        @else
            <p>Keranjang kosong.</p>
        @endif
    </div>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Midtrans Snap --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <script>
        $(function() {
            // Search destination
            $('#destination_search').on('input', function(){
                let query = $(this).val();
                if(query.length < 2){
                    $('#destination_list').hide();
                    return;
                }
                $.get("{{ route('checkout.searchDestination') }}", { query: query }, function(data){
                    if(data.length === 0){
                        $('#destination_list').html('<li class="p-2 text-gray-500">Tidak ditemukan</li>').show();
                        return;
                    }
                    let html = '';
                    data.forEach(item => {
                        html += `<li class="p-2 hover:bg-gray-100 cursor-pointer" 
                                   data-id="${item.id}" 
                                   data-province="${item.province_name}" 
                                   data-city="${item.city_name}" 
                                   data-district="${item.district_name}" 
                                   data-zip="${item.zip_code}">${item.label}</li>`;
                    });
                    $('#destination_list').html(html).show();
                });
            });

            // Pilih destination
            $(document).on('click', '#destination_list li', function() {
                $('#destination_search').val($(this).text());
                $('#destination_id').val($(this).data('id'));
                $('#destination_province').val($(this).data('province'));
                $('#destination_city').val($(this).data('city'));
                $('#destination_district').val($(this).data('district'));
                $('#destination_zip').val($(this).data('zip'));
                $('#destination_list').hide();

                // Load shipping options
                $.get("{{ route('checkout.shipping') }}", { destination_id: $(this).data('id') }, function(data) {
                    if(data.length === 0){
                        $('#shipping-options').html('<p class="text-red-500">Kurir tidak tersedia</p>');
                        $('#courier_option').val('');
                        return;
                    }
                    let grouped = {};
                    data.forEach(item => {
                        if(!grouped[item.courier]) grouped[item.courier] = [];
                        grouped[item.courier].push(item);
                    });

                    let html = '<label class="block mb-1 font-medium">Pilih Kurir:</label>';
                    for(let courier in grouped){
                        html += `<div class="mb-2"><strong>${courier}</strong><div class="ml-4">`;
                        grouped[courier].forEach(option => {
                            html += `<div class="mb-1">
                                        <input type="radio" name="courier_radio" value="${option.courier}|${option.service}|${option.cost}" required>
                                        ${option.service} | Rp ${option.cost.toLocaleString()} (Estimasi ${option.etd})
                                     </div>`;
                        });
                        html += `</div></div>`;
                    }
                    $('#shipping-options').html(html);

                    // Update hidden courier_option
                    $('input[name="courier_radio"]').change(function(){
                        $('#courier_option').val($(this).val());
                    });
                });
            });

            // Hide destination list saat klik di luar
            $(document).click(function(e){
                if(!$(e.target).closest('#destination_list, #destination_search').length){
                    $('#destination_list').hide();
                }
            });

            // Intercept form submit untuk bayar Midtrans
            $('#checkout-form').submit(function(e){
                e.preventDefault(); // jangan submit biasa
                let form = $(this);
                $.post(form.attr('action'), form.serialize(), function(res){
                    if(res.snap_token){
                        snap.pay(res.snap_token, {
                            onSuccess: function(result){ window.location.href='{{ route("products.index") }}'; },
                            onPending: function(result){ window.location.href='{{ route("products.index") }}'; },
                            onError: function(result){ alert('Pembayaran gagal'); }
                        });
                    } else {
                        alert('Gagal membuat transaksi.');
                    }
                }).fail(function(err){
                    console.log(err);
                    alert('Terjadi kesalahan. Cek console log.');
                });
            });
        });
    </script>
</x-app-layout>
