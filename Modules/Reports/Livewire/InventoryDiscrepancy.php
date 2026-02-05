<?php

namespace Modules\Reports\Livewire;

use App\Models\{Item, OperHead, JournalHead, JournalDetail, OperationItems};
use Modules\Accounts\Models\AccHead;
use Illuminate\Support\Facades\{DB, Auth};
use Livewire\Component;
use Modules\Settings\Models\PublicSetting;

class InventoryDiscrepancy extends Component
{
    public $warehouses;
    public $partners;
    public $selectedWarehouse;
    public $selectedPartner;
    public $inventoryData = [];
    public $quantities = [];
    public $hasUnsavedChanges = false;

    public $totalItems = 0;
    public $itemsWithShortage = 0;
    public $itemsWithOverage = 0;
    public $itemsMatching = 0;

    public $inventoryDifferenceAccount;
    public $inventoryDifferenceAccountValue; // القيمة المحفوظة في الإعدادات

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function hydrate()
    {
        if (empty($this->warehouses)) {
            $this->loadWarehouses();
        }
        if (empty($this->partners)) {
            $this->loadPartners();
        }
        if (is_null($this->inventoryDifferenceAccount)) {
            $this->loadInventoryDifferenceAccount();
        }
    }
    public function mount()
    {
        $this->loadWarehouses();
        $this->loadPartners();
        $this->loadInventoryDifferenceAccount();

        // Set default selections
        $this->selectedWarehouse = $this->warehouses->first()->id ?? null;
        $this->selectedPartner = $this->partners->first()->id ?? null;
        $this->refreshData();
    }

    /**
     * تحميل حساب الفروقات من الإعدادات العامة
     */
    public function loadInventoryDifferenceAccount()
    {
        try {
            $setting = PublicSetting::where('key', 'show_inventory_difference_account')->first();
            
            if (!$setting) {
                $this->inventoryDifferenceAccount = null;
                $this->inventoryDifferenceAccountValue = null;
                return;
            }

            $value = trim($setting->value ?? '');
            $this->inventoryDifferenceAccountValue = $value; // حفظ القيمة للعرض
            
            if (empty($value) || $value === '0' || $value === '') {
                $this->inventoryDifferenceAccount = null;
                return;
            }

            // محاولة البحث بالكود أولاً
            $account = AccHead::where('code', $value)
                ->where('isdeleted', 0)
                ->first();

            // إذا لم يُوجد بالكود، جرب البحث بالـ ID
            if (!$account && is_numeric($value)) {
                $account = AccHead::where('id', (int) $value)
                    ->where('isdeleted', 0)
                    ->first();
            }

            if (!$account) {
                // Account not found
            }

            $this->inventoryDifferenceAccount = $account ? $account->id : null;
        } catch (\Exception $e) {
            $this->inventoryDifferenceAccount = null;
            $this->inventoryDifferenceAccountValue = null;
        }
    }

    public function loadWarehouses()
    {
        try {
            $this->warehouses = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '1104%')
                ->select('id', 'aname')
                ->get();
        } catch (\Exception $e) {
            $this->warehouses = collect([]);
        }
    }

    public function loadPartners()
    {
        try {
            $this->partners = AccHead::where('isdeleted', 0)
                ->where('is_basic', 0)
                ->where('code', 'like', '3101%')
                ->select('id', 'aname')
                ->get();
        } catch (\Exception $e) {
            $this->partners = collect([]);
        }
    }

    /**
     * إعادة تحميل البيانات بشكل آمن لتجنب مشاكل Livewire hydration
     */
    public function safeRefreshData()
    {
        try {
            // إعادة تعيين البيانات بشكل آمن
            $this->inventoryData = [];
            $this->quantities = [];
            $this->hasUnsavedChanges = false;

            // إعادة تحميل البيانات الأساسية
            $this->hydrate();

            // تحديث البيانات
            $this->refreshData();
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ في تحديث البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate all data for the report.
     */
    public function refreshData()
    {
        try {
            $this->resetStatistics();

            if (!$this->selectedWarehouse) {
                $this->inventoryData = [];
                return;
            }

            $items = Item::with(['units'])->get();
            if ($items->isEmpty()) {
                $this->inventoryData = [];
                return;
            }

            $itemIds = $items->pluck('id')->toArray();
            $itemBalances = $this->calculateItemsBalance($itemIds, $this->selectedWarehouse);

            $tempData = [];
            foreach ($items as $item) {
                $systemQuantity = $itemBalances[$item->id] ?? 0;

                $actualQuantity = isset($this->quantities[$item->id])
                    ? (float) $this->quantities[$item->id]
                    : $systemQuantity;

                $discrepancy = $actualQuantity - $systemQuantity;
                $discrepancyType = $this->getDiscrepancyType($discrepancy);

                // استخدام أفضل تكلفة متاحة
                $itemCost = $item->average_cost ?? $item->cost ?? 0;

                $this->updateStatistics($discrepancy);

                $tempData[] = [
                    'item_id' => $item->id, // استخدام ID بدلاً من الكائن كاملاً
                    'item_name' => $item->name,
                    'item_cost' => (float) $itemCost,
                    'system_quantity' => (float) $systemQuantity,
                    'actual_quantity' => (float) $actualQuantity,
                    'discrepancy' => (float) $discrepancy,
                    'discrepancy_type' => $discrepancyType,
                    'discrepancy_value' => (float) ($discrepancy * $itemCost),
                    'main_unit_id' => $item->units->first()?->id,
                    'main_unit_name' => $item->units->first()?->name,
                ];

                // تعيين القيمة الافتراضية للكمية إذا لم تكن محددة
                if (!isset($this->quantities[$item->id])) {
                    $this->quantities[$item->id] = number_format($systemQuantity, 2, '.', '');
                }
            }

            $this->inventoryData = $tempData;
            $this->totalItems = count($this->inventoryData);
            $this->hasUnsavedChanges = false;
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ في تحديث البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Calculate balance for multiple items at once to avoid N+1 queries.
     */
    private function calculateItemsBalance(array $itemIds, $storeId): array
    {
        try {
            $balances = array_fill_keys($itemIds, 0);

            $results = OperationItems::whereIn('item_id', $itemIds)
                ->where('detail_store', $storeId)
                ->select('item_id', DB::raw('SUM(qty_in - qty_out) as balance'))
                ->groupBy('item_id')
                ->get();

            foreach ($results as $result) {
                $balances[$result->item_id] = (float) ($result->balance ?? 0);
            }

            return $balances;
        } catch (\Exception $e) {
            return array_fill_keys($itemIds, 0);
        }
    }

    /**
     * Livewire hook for when the selected warehouse changes.
     */
    public function updatedSelectedWarehouse()
    {
        $this->quantities = [];
        $this->safeRefreshData();
    }

    /**
     * تحسين دالة تحديث الكميات لإعادة حساب الإحصائيات
     */
    public function updatedQuantities($value, $key)
    {
        try {
            $itemId = (int) $key;
            $actualQuantity = is_numeric($value) ? (float) $value : 0;

            if (!is_numeric($actualQuantity)) {
                return;
            }

            $this->resetStatistics();

            $itemFound = false;
            foreach ($this->inventoryData as $index => $data) {
                if ($data['item_id'] == $itemId) {
                    $discrepancy = $actualQuantity - $data['system_quantity'];

                    // Safeguard for cost = 0 with shortage
                    if ($data['item_cost'] == 0 && $discrepancy < 0) {
                        // Option 1: Set discrepancy_value to 0 explicitly
                        $discrepancyValue = 0;

                        // Option 2: Log for debugging (add use Illuminate\Support\Facades\Log;)

                        // Or throw a custom alert
                        // $this->dispatch('show-alert', ['type' => 'warning', 'message' => 'تكلفة الصنف 0، لا يمكن حساب قيمة النقص.']);
                        // continue; // Skip this item if needed
                    } else {
                        $discrepancyValue = $discrepancy * $data['item_cost'];
                    }

                    $this->inventoryData[$index] = array_merge($this->inventoryData[$index], [
                        'actual_quantity' => $actualQuantity,
                        'discrepancy' => $discrepancy,
                        'discrepancy_type' => $this->getDiscrepancyType($discrepancy),
                        'discrepancy_value' => $discrepancyValue,
                    ]);

                    $itemFound = true;
                }

                $this->updateStatistics($this->inventoryData[$index]['discrepancy']);
            }

            if ($itemFound) {
                $this->hasUnsavedChanges = true;
            }
        } catch (\Exception $e) {
            $this->safeRefreshData();
        }
    }

    /**
     * دالة جديدة للحصول على ملخص القيم المالية
     */
    public function getFinancialSummary()
    {
        try {
            $totalIncreaseValue = 0;
            $totalDecreaseValue = 0;
            $netDifference = 0;

            foreach ($this->inventoryData as $data) {
                $discrepancyValue = $data['discrepancy_value'] ?? 0;

                if ($discrepancyValue > 0) {
                    $totalIncreaseValue += $discrepancyValue;
                } elseif ($discrepancyValue < 0) {
                    $totalDecreaseValue += abs($discrepancyValue);
                }

                $netDifference += $discrepancyValue;
            }

            return [
                'total_increase' => $totalIncreaseValue,
                'total_decrease' => $totalDecreaseValue,
                'net_difference' => $netDifference
            ];
        } catch (\Exception $e) {
            return [
                'total_increase' => 0,
                'total_decrease' => 0,
                'net_difference' => 0
            ];
        }
    }

    /**
     * This is the main function to apply the inventory adjustment.
     */
    public function applyInventoryAdjustments()
    {
        try {
            $this->validate([
                'selectedWarehouse' => 'required',
                'selectedPartner' => 'required'
            ], [
                'selectedWarehouse.required' => 'يجب اختيار المخزن.',
                'selectedPartner.required' => 'يجب اختيار حساب التسوية.'
            ]);

            // التحقق من وجود حساب الفروقات
            if (!$this->inventoryDifferenceAccount) {
                session()->flash('error', 'حساب فروقات الجرد غير محدد في الإعدادات العامة.');
                return;
            }

            DB::beginTransaction();

            $itemsToAdjust = array_filter($this->inventoryData, fn($data) => ($data['discrepancy'] ?? 0) != 0);

            if (empty($itemsToAdjust)) {
                session()->flash('info', 'لا توجد فروقات لتسويتها.');
                DB::rollBack();
                return;
            }

            // إنشاء عملية التسوية
            $operHead = OperHead::create([
                'pro_type' => 61,
                'acc1' => $this->selectedWarehouse,
                'acc2' => $this->selectedPartner,
                'pro_date' => now()->format('Y-m-d'),
                'store_id' => $this->selectedWarehouse,
                'pro_value' => 0,
                'is_stock' => 1,
                'is_journal' => 1,
                'info' => 'تسوية جرد المخزون - ' . now()->format('Y-m-d'),
                'user' => Auth::id(),
            ]);

            $totalAdjustmentValue = 0;
            $totalIncreaseValue = 0; // إجمالي الزيادات
            $totalDecreaseValue = 0; // إجمالي النقص

            // معالجة الأصناف وحساب التكاليف
            foreach ($itemsToAdjust as $data) {
                $itemId = $data['item_id'];
                $discrepancy = $data['discrepancy'];
                $unitCost = $data['item_cost']; // استخدام التكلفة المحسوبة مسبقاً
                $discrepancyValue = $discrepancy * $unitCost;

                // إضافة حركة المخزون
                OperationItems::create([
                    'pro_tybe' => 61,
                    'detail_store' => $this->selectedWarehouse,
                    'pro_id' => $operHead->id,
                    'item_id' => $itemId,
                    'unit_id' => $data['main_unit_id'] ?? null,
                    'unit_value' => 1.000,
                    'qty_in' => $discrepancy > 0 ? $discrepancy : 0,
                    'qty_out' => $discrepancy < 0 ? abs($discrepancy) : 0,
                    'item_price' => $unitCost,
                    'cost_price' => $unitCost,
                    'detail_value' => abs($discrepancyValue),
                ]);

                // تجميع القيم
                if ($discrepancy > 0) {
                    $totalIncreaseValue += $discrepancyValue;
                } else {
                    $totalDecreaseValue += abs($discrepancyValue);
                }

                $totalAdjustmentValue += $discrepancyValue;
            }

            $operHead->update(['pro_value' => abs($totalAdjustmentValue)]);

            // إنشاء القيود المحاسبية
            $this->createJournalEntries($operHead, $totalIncreaseValue, $totalDecreaseValue);

            DB::commit();
            session()->flash('success', 'تم تطبيق تسوية الجرد بنجاح.');
            $this->safeRefreshData();
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء القيود المحاسبية للفروقات
     */
    private function createJournalEntries($operHead, $totalIncreaseValue, $totalDecreaseValue)
    {
        try {
            if ($totalIncreaseValue == 0 && $totalDecreaseValue == 0) {
                return;
            }

            $journalId = (JournalHead::max('journal_id') ?? 0) + 1;
            $totalAmount = $totalIncreaseValue + $totalDecreaseValue;

            // حذف القيود السابقة إن وجدت
            JournalDetail::where('op_id', $operHead->id)->delete();
            JournalHead::where('op_id', $operHead->id)->where('pro_type', 61)->delete();

            // إنشاء رأس القيد
            JournalHead::updateOrCreate(
                ['journal_id' => $journalId, 'pro_type' => 61],
                [
                    'journal_id' => $journalId,
                    'total' => $totalAmount,
                    'date' => now()->format('Y-m-d'),
                    'op_id' => $operHead->id,
                    'pro_type' => 61,
                    'op2' => $operHead->id,
                    'user' => Auth::id(),
                ]
            );

            // قيد الزيادات (إن وجدت)
            if ($totalIncreaseValue > 0) {
                // مدين: المخزن (زيادة في المخزون)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $this->selectedWarehouse,
                    'debit' => $totalIncreaseValue,
                    'credit' => 0,
                    'type' => 1,
                    'op_id' => $operHead->id,
                ]);

                // دائن: حساب فروقات الجرد (إيراد من زيادة المخزون)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $this->inventoryDifferenceAccount,
                    'debit' => 0,
                    'credit' => $totalIncreaseValue,
                    'type' => 1,
                    'op_id' => $operHead->id,
                ]);
            }

            // قيد النقص (إن وجد)
            if ($totalDecreaseValue > 0) {
                // مدين: حساب فروقات الجرد (خسارة من نقص المخزون)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $this->inventoryDifferenceAccount,
                    'debit' => $totalDecreaseValue,
                    'credit' => 0,
                    'type' => 1,
                    'op_id' => $operHead->id,
                ]);

                // دائن: المخزن (نقص في المخزون)
                JournalDetail::create([
                    'journal_id' => $journalId,
                    'account_id' => $this->selectedWarehouse,
                    'debit' => 0,
                    'credit' => $totalDecreaseValue,
                    'type' => 1,
                    'op_id' => $operHead->id,
                ]);
            }

            // تحديث رصيد حساب الفروقات
            $this->updateInventoryDifferenceAccountBalance($totalIncreaseValue, $totalDecreaseValue);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * تحديث رصيد حساب فروقات الجرد
     */
    private function updateInventoryDifferenceAccountBalance($totalIncreaseValue, $totalDecreaseValue)
    {
        try {
            if (!$this->inventoryDifferenceAccount) {
                return;
            }

            // الحصول على الرصيد الحالي
            $currentBalance = JournalDetail::where('account_id', $this->inventoryDifferenceAccount)
                ->selectRaw('SUM(credit) - SUM(debit) as balance')
                ->value('balance') ?? 0;

            $newBalance = $currentBalance + $totalIncreaseValue - $totalDecreaseValue;

            // تحديث رصيد الحساب في جدول AccHead إذا كان له حقل balance
            $account = AccHead::find($this->inventoryDifferenceAccount);
            if ($account) {
                $account->update(['balance' => $newBalance]);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    // Helper methods
    private function resetStatistics()
    {
        $this->totalItems = 0;
        $this->itemsWithShortage = 0;
        $this->itemsWithOverage = 0;
        $this->itemsMatching = 0;
    }

    private function getDiscrepancyType($discrepancy)
    {
        if ($discrepancy > 0) return 'زيادة';
        if ($discrepancy < 0) return 'نقص';
        return 'مطابق';
    }

    private function updateStatistics($discrepancy)
    {
        if ($discrepancy > 0) {
            $this->itemsWithOverage++;
        } elseif ($discrepancy < 0) {
            $this->itemsWithShortage++;
        } else {
            $this->itemsMatching++;
        }
    }

    public function render()
    {
        return view('reports::livewire.inventory.inventory-discrepancy');
    }
}
