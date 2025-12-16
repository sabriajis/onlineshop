<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    public function show(Order $order)
    {
        $tracking_number = $order->shipment->tracking_number ?? null;
        $courier = $order->shipment->courier ?? null;

        if(!$tracking_number) return back()->with('error', 'Belum ada resi');

        $response = Http::get("https://api.rajaongkir.com/starter/waybill", [
            'key' => env('RAJAONGKIR_API_KEY'),
            'waybill' => $tracking_number,
            'courier' => $courier
        ]);

        $status = $response['rajaongkir']['result']['summary'] ?? [];
        return view('frontend.tracking.show', compact('order', 'status'));
    }
}
