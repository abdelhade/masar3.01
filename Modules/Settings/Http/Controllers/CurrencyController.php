<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Settings\Http\Requests\CurrencyRequest;
use Modules\Settings\Models\{Currency, ExchangeRate};

class CurrencyController extends Controller
{
    /**
     * Apply permissions middleware
     */
    public function __construct()
    {
        $this->middleware('permission:view Currencies')->only(['index']);
        $this->middleware('permission:create Currencies')->only(['create', 'store']);
        $this->middleware('permission:edit Currencies')->only(['edit', 'update']);
        $this->middleware('permission:delete Currencies')->only(['destroy']);
        // Exchange rate methods: updateRate, fetchLiveRate, updateMode - no permission required
    }

    /**
     * Display currencies list
     */
    public function index()
    {
        $currencies = Currency::with('latestRate')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('settings::currencies.index', compact('currencies'));
    }

    /**
     * Show create currency form
     */
    public function create()
    {
        return view('settings::currencies.create');
    }

    /**
     * Store new currency
     */
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

        Alert::toast(__('Currency added successfully'), 'success');
        return redirect()->route('currencies.index');
    }

    /**
     * Show edit currency form
     */
    public function edit(Currency $currency)
    {
        return view('settings::currencies.edit', compact('currency'));
    }

    /**
     * Update currency
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());

        Alert::toast(__('Currency updated successfully'), 'success');
        return redirect()->route('currencies.index');
    }

    /**
     * Delete currency
     */
    public function destroy(Currency $currency)
    {
        try {
            if ($currency->is_default) {
                Alert::toast(__('Cannot delete default currency'), 'error');
                return back();
            }

            $currency->delete();
            Alert::toast(__('Currency deleted successfully'), 'success');
        } catch (\Exception) {
            Alert::toast(__('An error occurred while deleting the currency'), 'error');
        }

        return redirect()->route('currencies.index');
    }

    /**
     * Update exchange rate manually
     */
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
                'message' => __('Rate updated successfully'),
                'rate' => number_format($request->rate, $currency->decimal_places),
                'rate_raw' => $request->rate
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred')
            ], 500);
        }
    }

    /**
     * Fetch live rate from API
     */
    public function fetchLiveRate(Currency $currency)
    {
        try {
            if ($currency->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => __('Default currency does not need exchange rate')
                ], 400);
            }

            $baseCurrency = Currency::default()->first();

            if (!$baseCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => __('Default currency must be set first')
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
                    'message' => __('Rate updated from API successfully')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __("Failed to fetch rate from API")
            ], 500);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred')
            ], 500);
        }
    }

    /**
     * Get exchange rate from API
     */
    private function getExchangeRateFromApi($baseCurrency, $targetCurrency)
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');

            if (!$apiKey) {
                return null;
            }

            if ($baseCurrency === 'USD') {
                $response = Http::timeout(30)->get('https://api.currencyapi.com/v3/latest', [
                    'apikey' => $apiKey,
                    'base_currency' => 'USD',
                    'currencies' => $targetCurrency
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['data'][$targetCurrency]['value'])) {
                        // Correct Logic: How much Base (e.g. EGP) equals 1 Target (e.g. USD)
                        // API returns: 1 USD = X Target.
                        // If Base is EGP and Target is USD: We want 47 (EGP).
                        // If API is based on USD, and target is EGP, it returns 47.
                        return $data['data'][$targetCurrency]['value'];
                    }
                }
            } else {
                $response = Http::timeout(30)->get('https://api.currencyapi.com/v3/latest', [
                    'apikey' => $apiKey,
                    'base_currency' => 'USD',
                    'currencies' => "{$baseCurrency},{$targetCurrency}"
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['data'][$baseCurrency]['value']) && isset($data['data'][$targetCurrency]['value'])) {
                        $baseRate = $data['data'][$baseCurrency]['value']; // 1 USD = X Base
                        $targetRate = $data['data'][$targetCurrency]['value']; // 1 USD = X Target

                        // Correct Logic: Value of 1 Target in Base
                        // Example: Base=EGP (47), Target=GBP (60)
                        // 1 GBP = (1/60) USD, 1 USD = 47 EGP -> 1 GBP = 47/60 EGP? No.
                        // 1 USD = 47 EGP, 1 USD = 0.8 GBP -> 1 GBP = 47 / 0.8 = 58.75 EGP.
                        return $baseRate / $targetRate;
                    }
                }
            }
            return null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get available currencies from API (No permission required - public endpoint)
     */
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

    /**
     * Fallback currencies list
     */
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

    /**
     * Update rate mode (automatic/manual)
     */
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
            'message' => __('Rate mode updated successfully')
        ]);
    }

    /**
     * Get conversion rate between currencies
     */
    public function getConversionRate(Request $request)
    {
        $request->validate([
            'from' => 'required|exists:currencies,code',
            'to' => 'required|exists:currencies,code',
            'amount' => 'nullable|numeric|min:0'
        ]);

        $fromCurrency = Currency::where('code', $request->from)->firstOrFail();
        $toCurrency = Currency::where('code', $request->to)->firstOrFail();

        // If same currency, no conversion needed
        if ($fromCurrency->id === $toCurrency->id) {
            return response()->json([
                'success' => true,
                'rate' => 1,
                'amount' => $request->amount ?? 0,
                'converted' => $request->amount ?? 0,
                'from' => $fromCurrency->code,
                'to' => $toCurrency->code
            ]);
        }

        $defaultCurrency = Currency::default()->first();

        // Get rates
        $fromRate = $fromCurrency->is_default ? 1 : ($fromCurrency->latestRate->rate ?? 1);
        $toRate = $toCurrency->is_default ? 1 : ($toCurrency->latestRate->rate ?? 1);

        // Calculate conversion rate
        // Correct Logic: (1 From = X Base) / (1 To = Y Base) -> 1 From = (X/Y) To
        // Example: From=USD (47), To=GBP (60) -> 1 USD = 47/60 GBP = 0.78 GBP
        $conversionRate = $fromRate / $toRate;

        $amount = $request->amount ?? 0;
        $converted = $amount * $conversionRate;

        return response()->json([
            'success' => true,
            'rate' => round($conversionRate, 8),
            'amount' => $amount,
            'converted' => round($converted, $toCurrency->decimal_places),
            'from' => $fromCurrency->code,
            'to' => $toCurrency->code,
            'from_symbol' => $fromCurrency->symbol,
            'to_symbol' => $toCurrency->symbol
        ]);
    }

    /**
     * Get all active currencies for dropdown
     */
    public function getActiveCurrencies()
    {
        $currencies = Currency::active()
            ->orderBy('is_default', 'desc')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol', 'decimal_places', 'is_default']);

        return response()->json([
            'success' => true,
            'currencies' => $currencies
        ]);
    }
}
