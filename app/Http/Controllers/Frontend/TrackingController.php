<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    public function show(Order $order)
    {
        // 🔐 pastikan order milik user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $shipment = $order->shipment;

        if (!$shipment || !$shipment->tracking_number) {
            return back()->with('error', 'Nomor resi belum tersedia');
        }

        try {
            Log::info('Tracking request sent', [
                'order_id' => $order->id,
                'waybill' => $shipment->tracking_number,
                'courier' => $shipment->courier
            ]);

            $response = Http::asForm()->withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->post('https://rajaongkir.komerce.id/api/v1/track/waybill', [
                'waybill' => $shipment->tracking_number,
                'courier' => strtolower($shipment->courier)
            ]);

            $result = $response->json();

            Log::info('Tracking response received', $result);

            if (!isset($result['data'])) {
                return back()->with('error', 'Data tracking tidak ditemukan');
            }

            // 🔄 update status otomatis (opsional tapi direkomendasikan)
            if (($result['data']['summary']['status'] ?? '') === 'DELIVERED') {
                $shipment->update([
                    'status' => 'delivered'
                ]);
            }

            return view('frontend.tracking.show', [
                'order' => $order,
                'shipment' => $shipment,
                'tracking' => $result['data']
            ]);

        } catch (\Exception $e) {
            Log::error('Tracking Error', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal mengambil data tracking');
        }
    }
}
