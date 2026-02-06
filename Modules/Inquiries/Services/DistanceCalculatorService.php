<?php

namespace Modules\Inquiries\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\HR\Models\City;
use Modules\HR\Models\Town;
use Modules\HR\Models\State;
use Modules\HR\Models\Country;

class DistanceCalculatorService
{
    // إحداثيات المقر (أبوظبي)
    private const HEADQUARTERS_LAT = 24.45388;
    private const HEADQUARTERS_LNG = 54.37734;
    private const HEADQUARTERS_ADDRESS = 'Abu Dhabi, UAE';

    /**
     * حساب المسافة مع التفاصيل (المسافة + الوقت)
     *
     * @param float $fromLat
     * @param float $fromLng
     * @param float $toLat
     * @param float $toLng
     * @return array|null ['distance' => float, 'duration' => string]
     */
    public function calculateDrivingDistanceWithDetails($fromLat, $fromLng, $toLat, $toLng)
    {
        $cacheKey = 'distance_' . $fromLat . '_' . $fromLng . '_to_' . $toLat . '_' . $toLng;

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($fromLat, $fromLng, $toLat, $toLng) {
            try {
                $apiKey = config('services.google_maps.api_key');
                if (!$apiKey) {
                    throw new \Exception('API Key لـ Google Maps غير موجود');
                }

                $body = [
                    'origin' => [
                        'location' => [
                            'latLng' => [
                                'latitude' => $fromLat,
                                'longitude' => $fromLng
                            ]
                        ]
                    ],
                    'destination' => [
                        'location' => [
                            'latLng' => [
                                'latitude' => $toLat,
                                'longitude' => $toLng
                            ]
                        ]
                    ],
                    'travelMode' => 'DRIVE',
                    'routingPreference' => 'TRAFFIC_AWARE',
                    'computeAlternativeRoutes' => false,
                    'units' => 'METRIC',
                    'languageCode' => 'ar'
                ];

                $response = Http::timeout(10)
                    ->withHeaders([
                        'X-Goog-Api-Key' => $apiKey,
                        'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration'
                    ])
                    ->post('https://routes.googleapis.com/directions/v2:computeRoutes', $body);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['routes'][0]['distanceMeters'])) {
                        $distanceInMeters = $data['routes'][0]['distanceMeters'];
                        $distanceKm = round($distanceInMeters / 1000, 2);

                        $duration = null;
                        if (isset($data['routes'][0]['duration'])) {
                            $durationSeconds = (int) rtrim($data['routes'][0]['duration'], 's');
                            $durationMinutes = round($durationSeconds / 60);
                            $duration = $durationMinutes . ' دقيقة';
                        }

                        Log::info('✅ تم حساب المسافة والوقت', [
                            'distance' => $distanceKm . ' كم',
                            'duration' => $duration
                        ]);

                        return [
                            'distance' => $distanceKm,
                            'duration' => $duration
                        ];
                    }
                }

                Log::warning('فشل Routes API', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error('خطأ في حساب المسافة: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * حساب المسافة على الطريق باستخدام Google Maps Routes API
     * ✅ تم إصلاح مشكلة FieldMask
     *
     * @param float $destinationLat خط العرض
     * @param float $destinationLng خط الطول
     * @return float|null المسافة بالكيلومتر
     */
    public function calculateDrivingDistance($destinationLat, $destinationLng)
    {
        $result = $this->calculateDrivingDistanceWithDetails(
            self::HEADQUARTERS_LAT,
            self::HEADQUARTERS_LNG,
            $destinationLat,
            $destinationLng
        );

        return $result ? $result['distance'] : null;
        try {
            $apiKey = config('services.google_maps.api_key');
            if (!$apiKey) {
                throw new \Exception('API Key لـ Google Maps غير موجود');
            }

            $body = [
                'origin' => [
                    'location' => [
                        'latLng' => [
                            'latitude' => self::HEADQUARTERS_LAT,
                            'longitude' => self::HEADQUARTERS_LNG
                        ]
                    ]
                ],
                'destination' => [
                    'location' => [
                        'latLng' => [
                            'latitude' => $destinationLat,
                            'longitude' => $destinationLng
                        ]
                    ]
                ],
                'travelMode' => 'DRIVE',
                'routingPreference' => 'TRAFFIC_AWARE',
                'computeAlternativeRoutes' => false,
                'units' => 'METRIC',
                'languageCode' => 'ar'
            ];

            // ✅ الإصلاح: إضافة X-Goog-FieldMask header
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration' // ✅ هذا هو الحل
                ])
                ->post('https://routes.googleapis.com/directions/v2:computeRoutes', $body);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Routes API Response', ['data' => $data]);

                if (isset($data['routes'][0]['distanceMeters'])) {
                    $distanceInMeters = $data['routes'][0]['distanceMeters'];
                    $distanceKm = round($distanceInMeters / 1000, 2);

                    Log::info('✅ تم حساب المسافة بنجاح', [
                        'from' => 'Abu Dhabi',
                        'to' => "{$destinationLat}, {$destinationLng}",
                        'distance' => $distanceKm . ' كم'
                    ]);

                    return $distanceKm;
                }
            }

            Log::warning('فشل Routes API', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('خطأ في حساب المسافة: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * حساب المسافة على الطريق باستخدام Google Maps Routes API وتخزين السجل في towns
     *
     * @param string $destinationAddress العنوان النصي (مثل: "Dubai Marina, UAE")
     * @return float|null المسافة بالكيلومتر
     */
    public function calculateAndStoreDrivingDistance($destinationAddress)
    {
        $cacheKey = 'distance_' . md5($destinationAddress);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($destinationAddress) {
            try {
                // 1. تحويل العنوان لإحداثيات (Geocoding)
                $coordinates = $this->getCoordinatesFromAddress($destinationAddress);
                if (!$coordinates) {
                    Log::warning('فشل Geocoding للعنوان: ' . $destinationAddress);
                    return null;
                }

                $destinationLat = $coordinates['latitude'];
                $destinationLng = $coordinates['longitude'];
                $formattedAddress = $coordinates['display_name'];

                // 2. حساب المسافة باستخدام Google Maps Routes API
                $distance = $this->calculateDrivingDistance($destinationLat, $destinationLng);
                if (!$distance) {
                    Log::warning('فشل حساب المسافة للعنوان: ' . $destinationAddress);
                    return null;
                }

                // 3. إضافة السجل في جدول towns
                $this->storeTown($destinationAddress, $destinationLat, $destinationLng, $distance);

                Log::info('✅ تم حساب المسافة وتخزين السجل', [
                    'address' => $destinationAddress,
                    'distance' => $distance . ' كم'
                ]);

                return $distance;
            } catch (\Exception $e) {
                Log::error('خطأ في حساب المسافة أو التخزين: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * تحويل العنوان لإحداثيات باستخدام Google Geocoding API
     */
    private function getCoordinatesFromAddress($address)
    {
        $cacheKey = 'geocode_' . md5($address);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($address) {
            try {
                $apiKey = config('services.google_maps.api_key');
                if (!$apiKey) {
                    throw new \Exception('API Key لـ Google Maps غير موجود');
                }

                $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $address . ', UAE',
                    'key' => $apiKey,
                    'language' => 'ar',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['results'][0]['geometry']['location']) && $data['status'] === 'OK') {
                        $location = $data['results'][0]['geometry']['location'];
                        return [
                            'latitude' => (float) $location['lat'],
                            'longitude' => (float) $location['lng'],
                            'display_name' => $data['results'][0]['formatted_address']
                        ];
                    }
                }

                throw new \Exception('فشل Geocoding: ' . ($data['status'] ?? 'No response'));
            } catch (\Exception $e) {
                Log::error('خطأ في Geocoding: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * تخزين السجل في جدول towns مع إنشاء/ربط country, state, city
     */
    private function storeTown($address, $latitude, $longitude, $distance)
    {
        // إنشاء/جلب سجل الدولة (United Arab Emirates)
        $country = Country::updateOrCreate(
            ['title' => 'United Arab Emirates'],
            ['title' => 'United Arab Emirates']
        );

        // إنشاء/جلب سجل الإمارات (State)
        $state = State::updateOrCreate(
            ['title' => 'United Arab Emirates', 'country_id' => $country->id],
            ['title' => 'United Arab Emirates', 'country_id' => $country->id]
        );

        // استخراج اسم الإمارة من العنوان
        $emirate = $this->extractEmirateFromAddress($address);
        $emirate = $emirate ?: 'Abu Dhabi'; // افتراضي إذا فشل الاستخراج

        // إنشاء/جلب سجل الإمارة (City)
        $emirates = [
            'Abu Dhabi' => ['latitude' => 24.45388, 'longitude' => 54.37734],
            'Dubai' => ['latitude' => 25.20485, 'longitude' => 55.27078],
            'Sharjah' => ['latitude' => 25.34625, 'longitude' => 55.42093],
            'Ajman' => ['latitude' => 25.40522, 'longitude' => 55.51364],
            'Umm Al Quwain' => ['latitude' => 25.54263, 'longitude' => 55.54754],
            'Ras Al Khaimah' => ['latitude' => 25.76416, 'longitude' => 55.96443],
            'Fujairah' => ['latitude' => 25.12881, 'longitude' => 56.32644],
        ];

        $cityData = $emirates[$emirate] ?? $emirates['Abu Dhabi'];
        $city = City::updateOrCreate(
            ['title' => $emirate, 'state_id' => $state->id],
            [
                'latitude' => $cityData['latitude'],
                'longitude' => $cityData['longitude'],
                'state_id' => $state->id
            ]
        );

        // إنشاء/تحديث سجل في towns
        Town::updateOrCreate(
            ['title' => $address],
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'city_id' => $city->id,
                'distance_from_headquarters' => $distance
            ]
        );
    }

    /**
     * استخراج اسم الإمارة من العنوان باستخدام Geocoding API
     */
    private function extractEmirateFromAddress($address)
    {
        $emirates = ['Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman', 'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'];

        // محاولة استخراج الإمارة من address_components في Geocoding API
        $apiKey = config('services.google_maps.api_key');
        if ($apiKey) {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address . ', UAE',
                'key' => $apiKey,
                'language' => 'ar',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['results'][0]['address_components'])) {
                    foreach ($data['results'][0]['address_components'] as $component) {
                        if (in_array($component['long_name'], $emirates)) {
                            return $component['long_name'];
                        }
                    }
                }
            }
        }

        // Fallback: البحث النصي البسيط
        foreach ($emirates as $emirate) {
            if (stripos($address, $emirate) !== false) {
                return $emirate;
            }
        }

        return null;
    }
}
