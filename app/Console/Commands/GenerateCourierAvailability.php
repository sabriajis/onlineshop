<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CourierAvailability;

class GenerateCourierAvailability extends Command
{
    protected $signature = 'courier:generate';
    protected $description = 'Generate available couriers per city/province';

    public function handle()
    {
        $this->info("Fetching provinces...");
        $provincesResponse = Http::withHeaders([
            'Key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        $provinces = $provincesResponse->json('data') ?? [];

        if (empty($provinces)) {
            $this->error("Tidak ada provinsi yang diterima dari API. Response: " . json_encode($provincesResponse->json()));
            return;
        }

        $origin = env('RAJAONGKIR_ORIGIN_CITY_ID');
        $availableCouriers = ['jne','tiki','pos'];
        $totalSaved = 0;

        foreach ($provinces as $province) {
            // Debug: tampilkan provinsi
            $this->info("Province raw: " . json_encode($province));

            $province_id = $province['id'] ?? $province['province_id'] ?? null;
            $province_name = $province['name'] ?? $province['province'] ?? 'Unknown';

            if (!$province_id) {
                $this->warn("Province ID not found for " . $province_name);
                continue;
            }

            $citiesResponse = Http::withHeaders([
                'Key' => env('RAJAONGKIR_API_KEY')
            ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$province_id}");

            $cities = $citiesResponse->json('data') ?? [];

            if (empty($cities)) {
                $this->warn("No cities found for province {$province_name} ({$province_id}). Response: " . json_encode($citiesResponse->json()));
                continue;
            }

            foreach ($cities as $city) {
                $city_id = $city['id'] ?? $city['city_id'] ?? null;
                $city_name = $city['name'] ?? $city['city_name'] ?? 'Unknown';

                if (!$city_id) {
                    $this->warn("City ID not found for city " . json_encode($city));
                    continue;
                }

                $this->info("Checking city {$city_name} ({$city_id})...");

                foreach ($availableCouriers as $courier) {
                    $response = Http::withHeaders([
                        'Key' => env('RAJAONGKIR_API_KEY')
                    ])->post('https://rajaongkir.komerce.id/api/v1/delivery/cost', [
                        'origin' => $origin,
                        'destination' => $city_id,
                        'weight' => 1000,
                        'courier' => $courier
                    ]);

                    $result = $response->json();

                    $available = !empty($result['data']['costs'] ?? null);

                    CourierAvailability::updateOrCreate(
                        [
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'courier' => $courier,
                        ],
                        [
                            'available' => $available
                        ]
                    );

                    $totalSaved++;
                    $this->info("Courier {$courier}: " . ($available ? 'Available' : 'Not Available'));
                }
            }
        }

        $this->info("Courier availability generation completed! Total records updated/created: {$totalSaved}");
    }
}
