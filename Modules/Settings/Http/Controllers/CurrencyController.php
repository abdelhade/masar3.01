<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Settings\Models\{Currency, ExchangeRate};
use Modules\Settings\Http\Requests\CurrencyRequest;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::with('latestRate')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('settings::currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('settings::currencies.create');
    }

    public function store(CurrencyRequest $request)
    {
        $currency = Currency::create($request->validated());

        if (!$currency->is_default && $request->filled('initial_rate')) {
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate' => $request->initial_rate,
                'rate_date' => today(),
            ]);
        }

        return redirect()
            ->route('currencies.index')
            ->with('success', 'Currency added successfully');
    }

    public function edit(Currency $currency)
    {
        return view('settings::currencies.edit', compact('currency'));
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());

        return redirect()
            ->route('currencies.index')
            ->with('success', 'Currency updated successfully');
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete default currency');
        }

        $currency->delete();

        return redirect()
            ->route('currencies.index')
            ->with('success', 'Currency deleted successfully');
    }

    public function updateRate(Request $request, Currency $currency)
    {
        try {
            $request->validate([
                'rate' => 'required|numeric|min:0.00000001|max:9999999999',
            ]);

            ExchangeRate::updateOrCreate(
                [
                    'currency_id' => $currency->id,
                    'rate_date' => today(),
                ],
                [
                    'rate' => $request->rate,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rate updated successfully',
                'rate' => number_format($request->rate, $currency->decimal_places),
                'rate_raw' => $request->rate
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    public function fetchLiveRate(Currency $currency)
    {
        try {
            if ($currency->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'Default currency does not need exchange rate'
                ], 400);
            }

            $baseCurrency = Currency::default()->first();

            if (!$baseCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'Default currency must be set first'
                ], 400);
            }

            $rate = $this->getExchangeRateFromApi($baseCurrency->code, $currency->code);

            if ($rate) {
                ExchangeRate::updateOrCreate(
                    [
                        'currency_id' => $currency->id,
                        'rate_date' => today(),
                    ],
                    [
                        'rate' => $rate,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'rate' => number_format($rate, $currency->decimal_places),
                    'rate_raw' => $rate,
                    'message' => 'Rate updated from API successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Failed to fetch rate from API"
            ], 500);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    private function getExchangeRateFromApi($baseCurrency, $targetCurrency)
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');

            if (!$apiKey) {
                return null;
            }

            if ($baseCurrency === 'USD') {
                $response = Http::timeout(10)->get('https://api.currencyapi.com/v3/latest', [
                    'apikey' => $apiKey,
                    'base_currency' => 'USD',
                    'currencies' => $targetCurrency
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['data'][$targetCurrency]['value'])) {
                        return $data['data'][$targetCurrency]['value'];
                    }
                }
            } else {
                $response = Http::timeout(10)->get('https://api.currencyapi.com/v3/latest', [
                    'apikey' => $apiKey,
                    'base_currency' => 'USD',
                    'currencies' => "{$baseCurrency},{$targetCurrency}"
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['data'][$baseCurrency]['value']) && isset($data['data'][$targetCurrency]['value'])) {
                        $baseRate = $data['data'][$baseCurrency]['value'];
                        $targetRate = $data['data'][$targetCurrency]['value'];

                        return $targetRate / $baseRate;
                    }
                }
            }
            return null;
        } catch (\Exception) {
            return null;
        }
    }

    public function getAvailableCurrencies()
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');

            if (!$apiKey) {
                return response()->json([
                    'success' => true,
                    'currencies' => $this->getFallbackCurrencies(),
                    'source' => 'fallback'
                ]);
            }

            $response = Http::timeout(15)->get('https://api.currencyapi.com/v3/currencies', [
                'apikey' => $apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) {
                    $currencies = [];

                    foreach ($data['data'] as $code => $currency) {
                        $currencies[] = [
                            'code' => $currency['code'] ?? $code,
                            'name' => $currency['name'] ?? $code,
                            'symbol' => $currency['symbol_native'] ?? $currency['symbol'] ?? $code
                        ];
                    }

                    usort($currencies, function ($a, $b) {
                        return strcmp($a['code'], $b['code']);
                    });

                    return response()->json([
                        'success' => true,
                        'currencies' => $currencies,
                        'source' => 'api'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'currencies' => $this->getFallbackCurrencies(),
                'source' => 'fallback'
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => true,
                'currencies' => $this->getFallbackCurrencies(),
                'source' => 'fallback'
            ]);
        }
    }

    private function getFallbackCurrencies()
    {
        return [
            // Arab currencies
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'ج.م'],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ'],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => 'ر.ق'],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => 'ر.ع'],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'د.ب'],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar', 'symbol' => 'د.أ'],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => 'ع.د'],
            ['code' => 'LBP', 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل'],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => 'ل.س'],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت'],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'د.م'],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'د.ج'],
            ['code' => 'LYD', 'name' => 'Libyan Dinar', 'symbol' => 'د.ل'],
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'symbol' => 'ج.س'],

            // Major world currencies
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '£'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'CNY', 'name' => 'Chinese Renminbi', 'symbol' => '¥'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽'],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R'],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$'],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$'],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$'],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$'],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩'],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr'],
            ['code' => 'PLN', 'name' => 'Polish Złoty', 'symbol' => 'zł'],
        ];
    }

    public function updateMode(Request $request, Currency $currency)
    {
        $request->validate([
            'rate_mode' => ['required', 'in:automatic,manual']
        ]);

        $currency->update([
            'rate_mode' => $request->rate_mode
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rate mode updated successfully'
        ]);
    }
}
