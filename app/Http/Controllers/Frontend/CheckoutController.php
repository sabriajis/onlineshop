<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    // Halaman checkout
    public function index()
    {
        $cart = session()->get('cart', []);
        if(empty($cart)){
            return redirect()->route('products.index')->with('error', 'Keranjang kosong');
        }

        return view('frontend.checkout.index', compact('cart'));
    }

    // Search destination (autocomplete)
    public function searchDestination(Request $request)
    {
        $query = $request->query('query');
        if(!$query) return response()->json([]);

        $response = Http::withHeaders([
            'Key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
            'search' => $query,
            'limit' => 5,
            'offset' => 0
        ]);

        $data = $response->json()['data'] ?? [];
        return response()->json($data);
    }

    // Hitung ongkir / shipping
    public function getShipping(Request $request)
{
    $destination_id = $request->destination_id;
    if (!$destination_id) {
        \Log::warning('Shipping calculation failed: Destination ID missing');
        return response()->json([]);
    }

    $cart = session()->get('cart', []);
    if (empty($cart)) {
        \Log::warning('Shipping calculation failed: Cart is empty');
        return response()->json([]);
    }

    $totalWeight = 0;
    foreach ($cart as $product_id => $item) {
        $product = Product::find($product_id);
        if ($product) {
            $weight_item = ($product->weight * 1000) * $item['qty'];
            $totalWeight += $weight_item;
            \Log::info("Cart Item - Product ID: {$product_id}, Qty: {$item['qty']}, Weight per item: {$product->weight} kg, Total weight item: {$weight_item} gr");
        }
    }
    if ($totalWeight < 1000) $totalWeight = 1000;
    \Log::info("Total weight for shipping calculation: {$totalWeight} gr");

    $allCouriers = 'jne:sicepat:jnt:pos:anteraja:ninja:tiki:lion:ncs:rex:star:wahana:dse';

    try {
        $response = Http::asForm()->withHeaders([
            'key' => env('RAJAONGKIR_API_KEY'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            'origin' => env('RAJAONGKIR_ORIGIN_CITY_ID'),
            'destination' => $destination_id,
            'weight' => $totalWeight,
            'courier' => $allCouriers,
            'price' => 'lowest'
        ]);

        $result = $response->json();
        \Log::info('Shipping API request successful', [
            'destination_id' => $destination_id,
            'request_payload' => [
                'origin' => env('RAJAONGKIR_ORIGIN_CITY_ID'),
                'weight' => $totalWeight,
                'courier' => $allCouriers
            ],
            'response' => $result
        ]);

        $shippingOptions = [];

        if(isset($result['data']) && is_array($result['data'])){
            foreach($result['data'] as $item){
                $courier = strtoupper($item['code'] ?? $item['name'] ?? 'UNKNOWN');

                // Jika ada 'costs'
                if(isset($item['costs']) && is_array($item['costs'])){
                    foreach($item['costs'] as $cost){
                        $shippingOptions[] = [
                            'courier' => $courier,
                            'service' => $cost['service'] ?? '-',
                            'cost' => $cost['cost'][0]['value'] ?? 0,
                            'etd' => $cost['cost'][0]['etd'] ?? '-'
                        ];
                    }
                } 
                // Jika tidak ada 'costs', ambil cost & etd langsung di level item
                else {
                    $shippingOptions[] = [
                        'courier' => $courier,
                        'service' => $item['service'] ?? '-',
                        'cost' => $item['cost'] ?? 0,
                        'etd' => $item['etd'] ?? '-'
                    ];
                }
            }
        }

        return response()->json($shippingOptions);

    } catch (\Exception $e) {
        \Log::error('Shipping API request failed', [
            'destination_id' => $destination_id,
            'exception_message' => $e->getMessage()
        ]);
        return response()->json(['error' => 'Gagal menghitung ongkir'], 500);
    }
}


    // Proses checkout dan pembayaran Midtrans
 public function process(Request $request)
{
    try {
        $cart = session()->get('cart', []);
        if(empty($cart)) {
            return response()->json(['error' => 'Keranjang kosong'], 400);
        }

        $request->validate([
            'destination.id' => 'required',
            'destination.province_name' => 'required',
            'destination.city_name' => 'required',
            'destination.district_name' => 'required',
            'courier_option' => 'required'
        ]);

        $total_price = collect($cart)->sum(fn($c) => $c['price'] * $c['qty']);
        list($courier, $service, $shipping_cost) = explode('|', $request->courier_option);
        $shipping_cost = (int) $shipping_cost;

        // Simpan order
        $order = Order::create([
            'user_id' => Auth::id(),
            'province_name' => $request->destination['province_name'],
            'city_name' => $request->destination['city_name'],
            'district_name' => $request->destination['district_name'],
            'zip_code' => $request->destination['zip_code'] ?? null,
            'total_price' => $total_price,
            'shipping_cost' => $shipping_cost,
            'courier' => strtoupper($courier).' - '.$service,
            'status' => 'pending',
        ]);

        // Simpan order items
        foreach($cart as $product_id => $item){
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product_id,
                'qty' => $item['qty'],
                'price' => $item['price']
            ]);
        }

        // Midtrans Snap
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $midtransTransaction = Snap::createTransaction([
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => $total_price + $shipping_cost
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email
            ]
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->id,
            'midtrans_token' => $midtransTransaction->token,
            'payment_type' => 'midtrans',
            'transaction_status' => 'pending',
            'gross_amount' => $total_price + $shipping_cost
        ]);

        session()->forget('cart');

        // Kembalikan JSON untuk AJAX
        return response()->json([
            'success' => true,
            'snap_token' => $midtransTransaction->token,
            'order_id' => $order->id
        ]);

    } catch (\Exception $e) {
        Log::error('Checkout Error: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat checkout. Cek log.',
            'error' => $e->getMessage()
        ], 500);
    }


}
}