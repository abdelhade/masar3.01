<?php

namespace App\Livewire\Traits;

use App\Models\Price;
use App\Enums\ItemType;
use App\Models\JournalDetail;
use App\Helpers\ItemViewModel;
use App\Models\OperationItems;
use App\Models\{OperHead, Item};
use Illuminate\Support\Collection;
use Modules\Accounts\Models\AccHead;

trait HandlesInvoiceData
{
    protected static array $accountCache = [];
    protected static array $mountCache = [];

    protected function initializeInvoiceData($type, $hash)
    {
        $cacheKey = "{$type}_{$hash}";

        if (!isset(static::$mountCache[$cacheKey])) {
            $this->branches = userBranches();
            if ($this->branches->isNotEmpty()) {
                $this->branch_id = $this->branches->first()->id;
                $this->loadBranchFilteredData($this->branch_id);
            }
            static::$mountCache[$cacheKey] = [
                'branches' => $this->branches,
                'branch_id' => $this->branch_id
            ];
        } else {
            $cached = static::$mountCache[$cacheKey];
            $this->branches = $cached['branches'];
            $this->branch_id = $cached['branch_id'];
            $this->loadBranchFilteredData($this->branch_id);
        }

        $this->type = (int) $type;
        if ($hash !== md5($this->type)) abort(403, 'نوع الفاتورة غير صحيح');
    }

    protected function handlePreviousStageData($sourceProid)
    {
        $sourceInvoice = OperHead::with(['operationItems.item.units', 'operationItems.item.prices'])
            ->where('pro_id', $sourceProid)
            ->first();

        if (!$sourceInvoice) {
            return;
        }

        $isFromRequestOrder = $sourceInvoice->pro_type == 25;

        // ✅ نقل الأصناف
        foreach ($sourceInvoice->operationItems as $item) {
            if (!$item->item || $item->item->units->isEmpty()) {
                continue;
            }

            $displayUnitId = $item->fat_unit_id ?? $item->unit_id;

            // 1. Fetch the u_val (conversion factor)
            $unit = $item->item->units->where('id', $displayUnitId)->first();
            $uVal = 1;

            if ($unit && $unit->pivot) {
                $uVal = $unit->pivot->u_val ?? 1;
            } else {
                // Fallback: fetch directly from database if not found in relation
                $pivotData = \Illuminate\Support\Facades\DB::table('item_units')
                    ->where('item_id', $item->item_id)
                    ->where('unit_id', $displayUnitId)
                    ->first();
                if ($pivotData) {
                    $uVal = $pivotData->u_val ?? 1;
                }
            }

            $baseQty = $item->qty_in ?: $item->qty_out;

            // القسمة على المعامل لترجع لأصلها (مثلاً 1000 كجم ÷ 1000 = 1 طن)
            $quantity = ($uVal > 0) ? ($baseQty / $uVal) : $baseQty;
            $price = $item->fat_price ?? $item->item_price;

            // ✅ جلب آخر سعر شراء إذا كان التحويل من طلب احتياج لأمر شراء
            if ($isFromRequestOrder && $this->type == 15) {
                $lastPurchasePrice = OperationItems::whereHas('operhead', function ($q) {
                    $q->where('pro_type', 11)->where('is_stock', 1);
                })
                    ->where('item_id', $item->item_id)
                    ->where('qty_in', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price');

                $price = $lastPurchasePrice ?? $item->item->average_cost ?? $price;
            }

            $this->invoiceItems[] = [
                'item_id' => $item->item_id,
                'name' => $item->item->name,
                'unit_id' => $displayUnitId,
                'quantity' => $quantity,
                'price' => $price,
                'discount' => $item->item_discount ?? 0,
                'sub_value' => $item->detail_value ?? ($price * $quantity),
                'available_units' => $item->item->units->map(fn($unit) => (object)[
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'u_val' => $unit->pivot->u_val ?? 1
                ]),
                'notes' => $item->notes
            ];
        }

        // ✅ نقل بيانات الفاتورة
        $this->acc1_id = request()->get('acc1') ?: $sourceInvoice->acc1;
        $this->acc2_id = request()->get('acc2') ?: $sourceInvoice->acc2;
        $this->emp_id = request()->get('emp_id') ?: $sourceInvoice->emp_id;
        $this->notes = request()->get('info') ?: $sourceInvoice->info;
        $this->branch_id = request()->get('branch_id') ?: $sourceInvoice->branch_id;
        $this->subtotal = $sourceInvoice->fat_total;
        $this->discount_percentage = $sourceInvoice->fat_disc_per ?? 0;
        $this->discount_value = $sourceInvoice->fat_disc ?? 0;
        $this->additional_percentage = $sourceInvoice->fat_plus_per ?? 0;
        $this->additional_value = $sourceInvoice->fat_plus ?? 0;
        $this->total_after_additional = $sourceInvoice->pro_value;
        // أضف نقل التواريخ:
        $this->pro_date = $sourceInvoice->pro_date;
        $this->accural_date = $sourceInvoice->accural_date;
    }

    protected function handleConvertData($convertData)
    {
        if (!isset($convertData['invoice_data'])) {
            return;
        }

        $invoiceData = $convertData['invoice_data'];

        // ✅ نقل جميع البيانات الأساسية
        $this->acc1_id = $invoiceData['supplier_id'] ?? $invoiceData['client_id'] ?? $this->acc1_id;
        $this->acc2_id = $invoiceData['store_id'] ?? $this->acc2_id;
        $this->emp_id = $invoiceData['employee_id'] ?? $this->emp_id;
        $this->delivery_id = $invoiceData['delivery_id'] ?? $this->delivery_id;
        $this->cash_box_id = $invoiceData['cash_box_id'] ?? $this->cash_box_id;
        $this->branch_id = $invoiceData['branch_id'] ?? $this->branch_id;
        $this->notes = $invoiceData['notes'] ?? '';
        $this->pro_date = $invoiceData['invoice_date'] ?? $this->pro_date;
        $this->accural_date = $invoiceData['accural_date'] ?? $this->accural_date;

        // ✅ نقل الإجماليات والخصومات
        $this->discount_percentage = $convertData['discount_percentage'] ?? 0;
        $this->additional_percentage = $convertData['additional_percentage'] ?? 0;
        $this->discount_value = $convertData['discount_value'] ?? 0;
        $this->additional_value = $convertData['additional_value'] ?? 0;
        $this->total_after_additional = $convertData['total_after_additional'] ?? 0;
        $this->subtotal = $convertData['subtotal'] ?? 0;

        // ✅ نقل الضرائب (إذا كانت موجودة)
        if (isset($convertData['tax_percentage'])) {
            $this->tax_percentage = $convertData['tax_percentage'];
        }
        if (isset($convertData['tax_value'])) {
            $this->tax_value = $convertData['tax_value'];
        }

        // ✅ نقل الأصناف مع كل التفاصيل
        if (isset($convertData['items_data']) && !empty($convertData['items_data'])) {
            $this->invoiceItems = collect($convertData['items_data'])
                ->filter(function ($item) {
                    // تصفية الأصناف حسب النوع إذا كانت فاتورة مشتريات
                    if (in_array($this->type, [11, 13, 15, 17])) {
                        $itemModel = Item::find($item['item_id']);
                        return $itemModel && $itemModel->type != ItemType::Service->value;
                    }
                    return true;
                })
                ->map(function ($item) {
                    // ✅ التأكد من نقل جميع البيانات
                    return [
                        'item_id' => $item['item_id'],
                        'unit_id' => $item['unit_id'],
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'discount' => $item['discount'] ?? 0,
                        'sub_value' => $item['sub_value'],
                        'available_units' => $item['available_units'],
                        'notes' => $item['notes'] ?? '',
                        'batch_number' => $item['batch_number'] ?? '',
                        'expiry_date' => $item['expiry_date'] ?? '',
                        'serial_numbers' => $item['serial_numbers'] ?? '',
                    ];
                })
                ->values()
                ->toArray();
        }

        // ✅ حساب الرصيد إذا كان مطلوب
        if (in_array($this->type, [10, 11, 12, 13]) && $this->acc1_id) {
            $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            $this->calculateBalanceAfterInvoice();
        }

        // حذف البيانات من الجلسة بعد النقل
        session()->forget('convert_invoice_data');

        // ✅ إرسال رسالة نجاح
        $this->dispatch(
            'success',
            title: 'تم التحميل بنجاح!',
            text: 'تم نقل جميع بيانات عرض السعر. يمكنك التعديل عليها الآن وحفظها كفاتورة مشتريات.',
            icon: 'success'
        );
    }

    protected function loadBranchFilteredData($branchId)
    {
        if (!$branchId) return;

        $clientsAccounts = $this->getAccountsByCodeAndBranch('1103%', $branchId);
        $suppliersAccounts = $this->getAccountsByCodeAndBranch('2101%', $branchId);
        $employeesAccounts = $this->getAccountsByCodeAndBranch('2102%', $branchId);
        $wasted = $this->getAccountsByCodeAndBranch('55%', $branchId);
        $accounts = $this->getAccountsByCodeAndBranch('1108%', $branchId);
        $stores = $this->getAccountsByCodeAndBranch('1104%', $branchId);

        // ✅ تعيين أسماء الحسابات حسب نوع الفاتورة
        $map = [
            10 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            11 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            12 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            13 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            14 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            15 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            16 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            17 => ['acc1_role' => 'مورد', 'acc2_role' => 'مخزن'],
            18 => ['acc1_role' => 'تالف', 'acc2_role' => 'مخزن'],
            19 => ['acc1_role' => 'حساب', 'acc2_role' => 'مخزن'],
            20 => ['acc1_role' => 'حساب', 'acc2_role' => 'مخزن'],
            21 => ['acc1_role' => 'مخزن من', 'acc2_role' => 'مخزن إلى'],
            22 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
            24 => ['acc1_role' => 'مصروف', 'acc2_role' => 'مورد'],
            25 => ['acc1_role' => 'مصروف', 'acc2_role' => 'مخزن'],
            26 => ['acc1_role' => 'عميل', 'acc2_role' => 'مخزن'],
        ];
        $this->acc1Role = $map[$this->type]['acc1_role'] ?? 'الحساب الأول';
        $this->acc2Role = $map[$this->type]['acc2_role'] ?? 'الحساب الثاني';

        // ✅ تحقق من الإعداد
        $allowAllClientTypes = setting('invoice_enable_all_client_types') == '1';

        // القيم المجمعة (للاستخدام عند تفعيل الإعداد)
        $mergedAccounts = null;
        if ($allowAllClientTypes) {
             $mergedAccounts = collect()
                ->merge($clientsAccounts)
                ->merge($suppliersAccounts)
                ->merge($employeesAccounts)
                ->unique('id')
                ->values();
        }

        // تحديد acc1 حسب نوع الفاتورة
        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            // فواتير المبيعات
            $this->acc1List = $allowAllClientTypes ? $mergedAccounts : $clientsAccounts;
        } elseif (in_array($this->type, [11, 13, 15, 17])) {
            // فواتير المشتريات
            $this->acc1List = $allowAllClientTypes ? $mergedAccounts : $suppliersAccounts;
        } elseif ($this->type == 18) {
            $this->acc1List = $wasted;
        } elseif (in_array($this->type, [19, 20])) {
            $this->acc1List = $accounts;
        } elseif ($this->type == 21) {
            $this->acc1List = $stores;
        } elseif ($this->type == 25) {
            $this->acc1List = $this->getAccountsByCodeAndBranch('53%', $branchId);
        } elseif ($this->type == 24) {
            $this->acc1List = $this->getAccountsByCodeAndBranch('5%', $branchId);
        }

        // باقي الكود كما هو 👇
        $this->acc2List = $this->type == 24 ? $suppliersAccounts : $stores;
        $this->employees = $this->getAccountsByCodeAndBranch('2102%', $branchId);
        $this->deliverys = $this->getAccountsByCodeAndBranch('2102%', $branchId);

        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->where('branch_id', $branchId)
            ->select('id', 'aname')
            ->get();

        $this->items = Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->when(in_array($this->type, [11, 13, 15, 17]), function ($query) {
                $query->where('type', ItemType::Inventory->value);
            })
            ->when($this->type == 24, function ($query) {
                $query->where('type', ItemType::Service->value);
            })
            ->take(20)
            ->get()
            ->toArray();
    }

    protected function getAccountsByCodeAndBranch(string $code, $branchId)
    {
        $cacheKey = $code . '_' . $branchId;

        if (!isset(static::$accountCache[$cacheKey])) {
            static::$accountCache[$cacheKey] = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', $code)
                ->where('branch_id', $branchId)
                ->select('id', 'code', 'aname')
                ->orderBy('id')
                ->get();
        }

        return static::$accountCache[$cacheKey];
    }

    protected function getAccountBalance($accountId)
    {
        $balance = JournalDetail::where('account_id', $accountId)
            ->where('isdeleted', 0)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;

        // if (($this->settings['allow_zero_opening_balance'] ?? '0') != '1' && $balance == 0 && $accountId) {
        //     $this->dispatch(
        //         'error',
        //         title: 'خطأ!',
        //         text: 'الرصيد الافتتاحي لا يمكن أن يكون صفرًا.',
        //         icon: 'error'
        //     );
        // }
        return $balance;
    }

    protected function resetSelectedValues()
    {
        $this->acc2_id = $this->acc2List->first()->id ?? null;
        $this->emp_id = $this->employees->first()->id ?? null;
        $this->delivery_id = $this->deliverys->first()->id ?? null;
        $this->cash_box_id = $this->cashAccounts->first()->id ?? null;
    }

    /** @return Collection<int, \App\Models\Item> */
    protected function getAvailableItems($branchId)
    {
        return Item::with(['units' => fn($q) => $q->orderBy('pivot_u_val'), 'prices'])
            ->when(in_array($this->type, [11, 13, 15, 17]), function ($query) {
                $query->where('type', ItemType::Inventory->value); // فقط الأصناف المخزنية لفواتير المشتريات
            })->take(20)->get();
    }

    protected function setDefaultValues()
    {
        // القيم الافتراضية
        $this->nextProId = OperHead::max('pro_id') + 1 ?? 1;
        $this->pro_id = $this->nextProId;
        $this->pro_date = now()->format('Y-m-d');
        $this->accural_date = now()->format('Y-m-d');

        $this->emp_id = 65;
        $this->cash_box_id = 59;
        $this->delivery_id = 65;
        $this->status = 0;

        if (in_array($this->type, [10, 12, 14, 16, 22])) {
            $this->acc1_id = 61;
            $this->acc2_id = 62;
        } elseif (in_array($this->type, [11, 13, 15, 17])) {
            $this->acc1_id = $this->acc1List->first()->id ?? null; // ⬅️ هنا التعديل
            $this->acc2_id = $this->acc2List->first()->id ?? null;
        } elseif (in_array($this->type, [18, 19, 20])) {
            $this->acc1_id = null;
            $this->acc2_id = 62;
        } elseif ($this->type == 24) {
            // Service invoice: acc1 is expenses, acc2 is supplier
            $this->acc1_id = $this->acc1List->first()->id ?? null;
            $this->acc2_id = $this->acc2List->first()->id ?? null;
        } elseif ($this->type == 25) {
            // Request order (طلب احتياج): acc1 should default to the mapped expenses account
            $this->acc1_id = $this->acc1List->first()->id ?? null;
            // acc2 defaults to stores list (as set above)
            $this->acc2_id = $this->acc2List->first()->id ?? null;
        } elseif ($this->type == 21) { // تحويل من مخزن لمخزن
            $this->acc1_id = null;
            $this->acc2_id = null;
        }
    }

    public function initializeInvoice($type, $hash)
    {
        $this->initializeInvoiceData($type, $hash);

        // ✅ 1. التحقق من وجود بيانات تحويل من عرض سعر أو فاتورة أخرى
        $convertData = session()->get('convert_invoice_data');
        if ($convertData) {
            $this->handleConvertData($convertData);
            return;
        }

        // 2. التحقق من وجود بيانات من المرحلة السابقة
        $sourceProid = request()->get('source_pro_id');
        if ($sourceProid) {
            $this->handlePreviousStageData($sourceProid);
            return;
        }

        // 3. تهيئة بيانات الفاتورة الافتراضية
        $this->setDefaultValues();

        // 4. تحميل البيانات الضرورية
        $this->loadInvoiceData();

        // 5. تحديد حالة عرض الرصيد
        $this->showBalance = in_array($this->type, [10, 11, 12, 13]);
        if ($this->showBalance && $this->acc1_id) {
            $this->currentBalance = $this->getAccountBalance($this->acc1_id);
            $this->calculateBalanceAfterInvoice();
        }

        // 6. تحميل توصيات الأصناف للعميل (فقط لفواتير المبيعات)
        if ($this->type == 10 && $this->acc1_id) {
            $this->recommendedItems = $this->getRecommendedItems($this->acc1_id);
        }
    }

    protected function loadInvoiceData()
    {
        // تحميل البيانات الأساسية
        $this->deliverys = $this->getAccountsByCode('2102%');
        $this->cashAccounts = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('is_fund', 1)
            ->select('id', 'aname')
            ->get();

        $this->cashClientIds = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '110301%')
            ->pluck('id')
            ->toArray();

        $this->cashSupplierIds = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '210101%')
            ->pluck('id')
            ->toArray();

        $this->employees = $this->getAccountsByCode('2102%');
        $this->priceTypes = Price::pluck('name', 'id')->toArray();
        $this->searchResults = collect();
        $this->barcodeSearchResults = collect();
    }

    protected function getAccountsByCode(string $code)
    {
        return AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', $code)
            ->select('id', 'aname')
            ->get();
    }

    /**
     * حساب سعر الصنف بناءً على نوع الفاتورة والوحدة المختارة
     */
    protected function calculateItemPrice($item, $unitId, $priceTypeId = 1, $currentPrice = 0, $oldUnitId = null)
    {
        if (!$item || !$unitId) {
            return 0;
        }

        // ✅ منطق التحويل الديناميكي: إذا كان هناك سعر مكتوب ووحدة سابقة، قم بالتحويل
        if ($currentPrice > 0 && $oldUnitId && $oldUnitId != $unitId) {
            $oldUnit = $item->units->where('id', $oldUnitId)->first();
            $newUnit = $item->units->where('id', $unitId)->first();

            if ($oldUnit && $newUnit) {
                $oldUVal = $oldUnit->pivot->u_val ?? 1;
                $newUVal = $newUnit->pivot->u_val ?? 1;

                // السعر الأساسي (للوحدة الصغرى)
                $basePrice = $currentPrice / $oldUVal;

                // السعر الجديد (للوحدة الجديدة)
                return $basePrice * $newUVal;
            }
        }

        $price = 0;

        // 1. منطق فواتير المشتريات وأوامر الشراء (11, 15)
        if (in_array($this->type, [11, 15])) {
            // محاولة جلب آخر سعر شراء لنفس الصنف ونفس الوحدة
            $lastPurchasePrice = OperationItems::where('item_id', $item->id)
                ->where('unit_id', $unitId) // ✅ تصفية حسب الوحدة
                ->where('is_stock', 1)
                ->whereIn('pro_tybe', [11, 20]) // عمليات الشراء والإضافة للمخزن
                ->where('qty_in', '>', 0)
                ->orderBy('created_at', 'desc')
                ->value('item_price');

            if ($lastPurchasePrice && $lastPurchasePrice > 0) {
                $price = $lastPurchasePrice;
            } else {
                // إذا لم يوجد سعر سابق لهذه الوحدة، نحسب بناءً على التكلفة المتوسطة ومعامل التحويل
                $unit = $item->units->where('id', $unitId)->first();
                $uVal = $unit->pivot->u_val ?? 1;
                $averageCost = $item->average_cost ?? 0;
                $price = $averageCost * $uVal;
            }
        }
        // 2. منطق فواتير التوالف (18)
        elseif ($this->type == 18) {
            $unit = $item->units->where('id', $unitId)->first();
            $uVal = $unit->pivot->u_val ?? 1;
            $averageCost = $item->average_cost ?? 0;
            $price = $averageCost * $uVal;
        }
        // 3. منطق فواتير المبيعات وغيرها
        else {
            $vm = new ItemViewModel(null, $item, $unitId);
            $salePrices = $vm->getUnitSalePrices();
            $price = $salePrices[$priceTypeId]['price'] ?? 0;

            // تطبيق منطق اتفاقيات التسعير وآخر سعر للعميل (فقط للمبيعات)
            if ($this->type == 10 && $this->acc1_id) {
                $usePricingAgreement = (setting('invoice_use_pricing_agreement') ?? '0') == '1';
                $useLastCustomerPrice = (setting('invoice_use_last_customer_price') ?? '0') == '1';

                if (!($usePricingAgreement && $useLastCustomerPrice)) {
                    if ($usePricingAgreement) {
                        $pricingAgreementPrice = OperationItems::whereHas('operhead', function ($query) {
                            $query->where('pro_type', 26)->where('acc1', $this->acc1_id);
                        })
                            ->where('item_id', $item->id)
                            ->where('unit_id', $unitId)
                            ->orderBy('created_at', 'desc')
                            ->value('item_price');

                        if ($pricingAgreementPrice && $pricingAgreementPrice > 0) {
                            $price = $pricingAgreementPrice;
                        }
                    } elseif ($useLastCustomerPrice) {
                        $lastCustomerPrice = OperationItems::whereHas('operhead', function ($query) {
                            $query->where('pro_type', 10)->where('acc1', $this->acc1_id);
                        })
                            ->where('item_id', $item->id)
                            ->where('unit_id', $unitId)
                            ->orderBy('created_at', 'desc')
                            ->value('item_price');

                        if ($lastCustomerPrice && $lastCustomerPrice > 0) {
                            $price = $lastCustomerPrice;
                        }
                    }
                }
            }
            // اتفاقيات التسعير (26)
            elseif ($this->type == 26 && $this->acc1_id) {
                $pricingAgreementPrice = OperationItems::whereHas('operhead', function ($query) {
                    $query->where('pro_type', 26)->where('acc1', $this->acc1_id);
                })
                    ->where('item_id', $item->id)
                    ->where('unit_id', $unitId)
                    ->orderBy('created_at', 'desc')
                    ->value('item_price');

                if ($pricingAgreementPrice && $pricingAgreementPrice > 0) {
                    $price = $pricingAgreementPrice;
                }
            }
        }

        return $price;
    }
}
