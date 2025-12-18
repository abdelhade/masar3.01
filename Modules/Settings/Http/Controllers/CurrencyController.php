<?php

namespace Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Settings\Models\{Currency, ExchangeRate};
use Modules\Settings\Http\Requests\CurrencyRequest;

class CurrencyController extends Controller
{
    /**
     * عرض قائمة العملات مع أسعار الصرف
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
     * عرض صفحة إضافة عملة جديدة
     */
    public function create()
    {
        return view('settings::currencies.create');
    }

    public function store(CurrencyRequest $request)
    {
        $currency = Currency::create($request->validated());

        // لو العملة مش default وفي سعر ابتدائي، احفظه
        if (!$currency->is_default && $request->filled('initial_rate')) {
            ExchangeRate::create([
                'currency_id' => $currency->id,
                'rate' => $request->initial_rate,
                'rate_date' => today(),
            ]);
        }

        return redirect()
            ->route('currencies.index')
            ->with('success', 'تم إضافة العملة بنجاح');
    }

    /**
     * عرض صفحة تعديل عملة
     */
    public function edit(Currency $currency)
    {
        return view('settings::currencies.edit', compact('currency'));
    }

    /**
     * تحديث بيانات عملة
     */
    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());

        return redirect()
            ->route('currencies.index')
            ->with('success', 'تم تحديث العملة بنجاح');
    }

    /**
     * حذف عملة
     */
    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'لا يمكن حذف العملة الافتراضية');
        }

        $currency->delete();

        return redirect()
            ->route('currencies.index')
            ->with('success', 'تم حذف العملة بنجاح');
    }

    /**
     * تحديث سعر الصرف يدوياً (Manual Rate)
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
                'message' => 'تم تحديث السعر بنجاح',
                'rate' => number_format($request->rate, $currency->decimal_places),
                'rate_raw' => $request->rate
            ]);
        } catch (\Exception) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '
            ], 500);
        }
    }


    /**
     * جلب سعر الصرف من API (Automatic)
     */
    public function fetchLiveRate(Currency $currency)
    {
        try {
            if ($currency->is_default) {
                return response()->json([
                    'success' => false,
                    'message' => 'العملة الافتراضية لا تحتاج لسعر صرف'
                ], 400);
            }

            $baseCurrency = Currency::default()->first();

            if (!$baseCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد عملة افتراضية أولاً'
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
                    'message' => 'تم تحديث السعر من API بنجاح'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "فشل الحصول على السعر من API"
            ], 500);
        } catch (\Exception) {

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '
            ], 500);
        }
    }


    /**
     * الحصول على سعر الصرف من API
     */
    private function getExchangeRateFromApi($baseCurrency, $targetCurrency)
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');

            if (!$apiKey) {
                return null;
            }

            if ($baseCurrency === 'USD') {
                // لو الـ base هو USD، جيب مباشرة
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
                // لو الـ base مش USD، جيب الاتنين مقابل USD واحسب النسبة
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

                        // احسب: 1 baseCurrency = كام targetCurrency
                        $rate = $targetRate / $baseRate;


                        return $rate;
                    }
                }
            }
            return null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * جلب كل العملات المتاحة من CurrencyAPI
     */
    public function getAvailableCurrencies()
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');

            if (!$apiKey) {
                // استخدام Fallback
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

                    // ✅ قراءة البيانات بالـ structure الصحيح
                    foreach ($data['data'] as $code => $currency) {
                        $currencies[] = [
                            'code' => $currency['code'] ?? $code,
                            'name' => $currency['name'] ?? $code,
                            'symbol' => $currency['symbol_native'] ?? $currency['symbol'] ?? $code
                        ];
                    }

                    // ترتيب أبجدي
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

            // Fallback
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
     * قائمة العملات الاحتياطية (Fallback)
     */
    private function getFallbackCurrencies()
    {
        return [
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ'],
            ['code' => 'AFN', 'name' => 'Afghan Afghani', 'symbol' => '؋'],
            ['code' => 'ALL', 'name' => 'Albanian Lek', 'symbol' => 'L'],
            ['code' => 'AMD', 'name' => 'Armenian Dram', 'symbol' => '֏'],
            ['code' => 'ANG', 'name' => 'Netherlands Antillian Guilder', 'symbol' => 'ƒ'],
            ['code' => 'AOA', 'name' => 'Angolan Kwanza', 'symbol' => 'Kz'],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
            ['code' => 'AWG', 'name' => 'Aruban Florin', 'symbol' => 'ƒ'],
            ['code' => 'AZN', 'name' => 'Azerbaijani Manat', 'symbol' => '₼'],
            ['code' => 'BAM', 'name' => 'Bosnia and Herzegovina Mark', 'symbol' => 'KM'],
            ['code' => 'BBD', 'name' => 'Barbados Dollar', 'symbol' => '$'],
            ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => '৳'],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => 'лв'],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'د.ب'],
            ['code' => 'BIF', 'name' => 'Burundian Franc', 'symbol' => 'Fr'],
            ['code' => 'BMD', 'name' => 'Bermudian Dollar', 'symbol' => '$'],
            ['code' => 'BND', 'name' => 'Brunei Dollar', 'symbol' => '$'],
            ['code' => 'BOB', 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs.'],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$'],
            ['code' => 'BSD', 'name' => 'Bahamian Dollar', 'symbol' => '$'],
            ['code' => 'BTN', 'name' => 'Bhutanese Ngultrum', 'symbol' => 'Nu.'],
            ['code' => 'BWP', 'name' => 'Botswana Pula', 'symbol' => 'P'],
            ['code' => 'BYN', 'name' => 'Belarusian Ruble', 'symbol' => 'Br'],
            ['code' => 'BZD', 'name' => 'Belize Dollar', 'symbol' => '$'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
            ['code' => 'CDF', 'name' => 'Congolese Franc', 'symbol' => 'Fr'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF'],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$'],
            ['code' => 'CNY', 'name' => 'Chinese Renminbi', 'symbol' => '¥'],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$'],
            ['code' => 'CRC', 'name' => 'Costa Rican Colon', 'symbol' => '₡'],
            ['code' => 'CUP', 'name' => 'Cuban Peso', 'symbol' => '$'],
            ['code' => 'CVE', 'name' => 'Cape Verdean Escudo', 'symbol' => '$'],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč'],
            ['code' => 'DJF', 'name' => 'Djiboutian Franc', 'symbol' => 'Fr'],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr'],
            ['code' => 'DOP', 'name' => 'Dominican Peso', 'symbol' => '$'],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'د.ج'],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'ج.م'],
            ['code' => 'ERN', 'name' => 'Eritrean Nakfa', 'symbol' => 'Nfk'],
            ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'FJD', 'name' => 'Fiji Dollar', 'symbol' => '$'],
            ['code' => 'FKP', 'name' => 'Falkland Islands Pound', 'symbol' => '£'],
            ['code' => 'FOK', 'name' => 'Faroese Króna', 'symbol' => 'kr'],
            ['code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '£'],
            ['code' => 'GEL', 'name' => 'Georgian Lari', 'symbol' => '₾'],
            ['code' => 'GGP', 'name' => 'Guernsey Pound', 'symbol' => '£'],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => '₵'],
            ['code' => 'GIP', 'name' => 'Gibraltar Pound', 'symbol' => '£'],
            ['code' => 'GMD', 'name' => 'Gambian Dalasi', 'symbol' => 'D'],
            ['code' => 'GNF', 'name' => 'Guinean Franc', 'symbol' => 'Fr'],
            ['code' => 'GTQ', 'name' => 'Guatemalan Quetzal', 'symbol' => 'Q'],
            ['code' => 'GYD', 'name' => 'Guyanese Dollar', 'symbol' => '$'],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$'],
            ['code' => 'HNL', 'name' => 'Honduran Lempira', 'symbol' => 'L'],
            ['code' => 'HRK', 'name' => 'Croatian Kuna', 'symbol' => 'kn'],
            ['code' => 'HTG', 'name' => 'Haitian Gourde', 'symbol' => 'G'],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft'],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
            ['code' => 'ILS', 'name' => 'Israeli New Shekel', 'symbol' => '₪'],
            ['code' => 'IMP', 'name' => 'Manx Pound', 'symbol' => '£'],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => 'ع.د'],
            ['code' => 'IRR', 'name' => 'Iranian Rial', 'symbol' => '﷼'],
            ['code' => 'ISK', 'name' => 'Icelandic Króna', 'symbol' => 'kr'],
            ['code' => 'JEP', 'name' => 'Jersey Pound', 'symbol' => '£'],
            ['code' => 'JMD', 'name' => 'Jamaican Dollar', 'symbol' => '$'],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar', 'symbol' => 'د.أ'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'Sh'],
            ['code' => 'KGS', 'name' => 'Kyrgyzstani Som', 'symbol' => 'с'],
            ['code' => 'KHR', 'name' => 'Cambodian Riel', 'symbol' => '៛'],
            ['code' => 'KID', 'name' => 'Kiribati Dollar', 'symbol' => '$'],
            ['code' => 'KMF', 'name' => 'Comorian Franc', 'symbol' => 'Fr'],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩'],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك'],
            ['code' => 'KYD', 'name' => 'Cayman Islands Dollar', 'symbol' => '$'],
            ['code' => 'KZT', 'name' => 'Kazakhstani Tenge', 'symbol' => '₸'],
            ['code' => 'LAK', 'name' => 'Lao Kip', 'symbol' => '₭'],
            ['code' => 'LBP', 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل'],
            ['code' => 'LKR', 'name' => 'Sri Lanka Rupee', 'symbol' => 'Rs'],
            ['code' => 'LRD', 'name' => 'Liberian Dollar', 'symbol' => '$'],
            ['code' => 'LSL', 'name' => 'Lesotho Loti', 'symbol' => 'L'],
            ['code' => 'LYD', 'name' => 'Libyan Dinar', 'symbol' => 'د.ل'],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'د.م'],
            ['code' => 'MDL', 'name' => 'Moldovan Leu', 'symbol' => 'L'],
            ['code' => 'MGA', 'name' => 'Malagasy Ariary', 'symbol' => 'Ar'],
            ['code' => 'MKD', 'name' => 'Macedonian Denar', 'symbol' => 'ден'],
            ['code' => 'MMK', 'name' => 'Burmese Kyat', 'symbol' => 'K'],
            ['code' => 'MNT', 'name' => 'Mongolian Tögrög', 'symbol' => '₮'],
            ['code' => 'MOP', 'name' => 'Macanese Pataca', 'symbol' => 'P'],
            ['code' => 'MRU', 'name' => 'Mauritanian Ouguiya', 'symbol' => 'UM'],
            ['code' => 'MUR', 'name' => 'Mauritian Rupee', 'symbol' => '₨'],
            ['code' => 'MVR', 'name' => 'Maldivian Rufiyaa', 'symbol' => 'ރ.'],
            ['code' => 'MWK', 'name' => 'Malawian Kwacha', 'symbol' => 'MK'],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$'],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            ['code' => 'MZN', 'name' => 'Mozambican Metical', 'symbol' => 'MT'],
            ['code' => 'NAD', 'name' => 'Namibian Dollar', 'symbol' => '$'],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦'],
            ['code' => 'NIO', 'name' => 'Nicaraguan Córdoba', 'symbol' => 'C$'],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr'],
            ['code' => 'NPR', 'name' => 'Nepalese Rupee', 'symbol' => '₨'],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$'],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => 'ر.ع'],
            ['code' => 'PAB', 'name' => 'Panamanian Balboa', 'symbol' => 'B/.'],
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/.'],
            ['code' => 'PGK', 'name' => 'Papua New Guinean Kina', 'symbol' => 'K'],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱'],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => '₨'],
            ['code' => 'PLN', 'name' => 'Polish Złoty', 'symbol' => 'zł'],
            ['code' => 'PYG', 'name' => 'Paraguayan Guaraní', 'symbol' => '₲'],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => 'ر.ق'],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'lei'],
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'дин'],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽'],
            ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'Fr'],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س'],
            ['code' => 'SBD', 'name' => 'Solomon Islands Dollar', 'symbol' => '$'],
            ['code' => 'SCR', 'name' => 'Seychellois Rupee', 'symbol' => '₨'],
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'symbol' => 'ج.س'],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr'],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$'],
            ['code' => 'SHP', 'name' => 'Saint Helena Pound', 'symbol' => '£'],
            ['code' => 'SLE', 'name' => 'Sierra Leonean Leone', 'symbol' => 'Le'],
            ['code' => 'SOS', 'name' => 'Somali Shilling', 'symbol' => 'Sh'],
            ['code' => 'SRD', 'name' => 'Surinamese Dollar', 'symbol' => '$'],
            ['code' => 'SSP', 'name' => 'South Sudanese Pound', 'symbol' => '£'],
            ['code' => 'STN', 'name' => 'São Tomé and Príncipe Dobra', 'symbol' => 'Db'],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => 'ل.س'],
            ['code' => 'SZL', 'name' => 'Eswatini Lilangeni', 'symbol' => 'L'],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿'],
            ['code' => 'TJS', 'name' => 'Tajikistani Somoni', 'symbol' => 'ЅМ'],
            ['code' => 'TMT', 'name' => 'Turkmenistan Manat', 'symbol' => 'm'],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت'],
            ['code' => 'TOP', 'name' => 'Tongan Paʻanga', 'symbol' => 'T$'],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺'],
            ['code' => 'TTD', 'name' => 'Trinidad and Tobago Dollar', 'symbol' => '$'],
            ['code' => 'TVD', 'name' => 'Tuvaluan Dollar', 'symbol' => '$'],
            ['code' => 'TWD', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$'],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'Sh'],
            ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia', 'symbol' => '₴'],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'Sh'],
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$'],
            ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$'],
            ['code' => 'UZS', 'name' => 'Uzbekistani So\'m', 'symbol' => 'so\'m'],
            ['code' => 'VES', 'name' => 'Venezuelan Bolívar', 'symbol' => 'Bs.'],
            ['code' => 'VND', 'name' => 'Vietnamese Đồng', 'symbol' => '₫'],
            ['code' => 'VUV', 'name' => 'Vanuatu Vatu', 'symbol' => 'Vt'],
            ['code' => 'WST', 'name' => 'Samoan Tālā', 'symbol' => 'T'],
            ['code' => 'XAF', 'name' => 'Central African CFA Franc', 'symbol' => 'Fr'],
            ['code' => 'XCD', 'name' => 'East Caribbean Dollar', 'symbol' => '$'],
            ['code' => 'XDR', 'name' => 'Special Drawing Rights', 'symbol' => 'SDR'],
            ['code' => 'XOF', 'name' => 'West African CFA Franc', 'symbol' => 'Fr'],
            ['code' => 'XPF', 'name' => 'CFP Franc', 'symbol' => 'Fr'],
            ['code' => 'YER', 'name' => 'Yemeni Rial', 'symbol' => '﷼'],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R'],
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK'],
            ['code' => 'ZWL', 'name' => 'Zimbabwean Dollar', 'symbol' => '$'],
        ];
    }


    /**
     * الحصول على رمز العملة (Symbol)
     */
    private function getCurrencySymbol($code)
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'EGP' => 'ج.م',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
            'KWD' => 'د.ك',
            'QAR' => 'ر.ق',
            'OMR' => 'ر.ع',
            'BHD' => 'د.ب',
            'JOD' => 'د.أ',
            'IQD' => 'ع.د',
            'LBP' => 'ل.ل',
            'SYP' => 'ل.س',
            'TND' => 'د.ت',
            'MAD' => 'د.م',
            'DZD' => 'د.ج',
            'LYD' => 'د.ل',
            'SDG' => 'ج.س',
            'INR' => '₹',
            'CNY' => '¥',
            'RUB' => '₽',
            'TRY' => '₺',
            'CHF' => 'CHF',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'NZD' => 'NZ$',
            'ZAR' => 'R',
            'BRL' => 'R$',
            'MXN' => 'Mex$',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'PLN' => 'zł',
            'HUF' => 'Ft',
            'CZK' => 'Kč',
        ];

        return $symbols[$code] ?? $code;
    }



    /**
     * تحديث طريقة التحديث (Manual/Automatic)
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
            'message' => 'تم تحديث طريقة التحديث بنجاح'
        ]);
    }
}
