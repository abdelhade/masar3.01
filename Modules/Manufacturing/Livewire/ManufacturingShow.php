<?php

namespace Modules\Manufacturing\Livewire;

use App\Models\Expense;
use App\Models\OperHead;
use Livewire\Component;

class ManufacturingShow extends Component
{
    public $invoice;

    public $products = [];

    public $rawMaterials = [];

    public $expenses = [];

    public $totals = [];

    public function mount($id)
    {
        $this->loadInvoice($id);
    }

    private function loadInvoice($id)
    {
        $this->invoice = OperHead::with([
            'acc1Head',
            'acc2Head',
            'employee',
            'store',
            'branch',
            'operationItems.item',
            'operationItems.unit',
        ])->findOrFail($id);

        // تحميل جميع العناصر وفصلها
        $allItems = $this->invoice->operationItems()
            ->with(['item', 'unit'])
            ->get();

        $this->products = collect();
        $this->rawMaterials = collect();

        foreach ($allItems as $item) {
            $qtyIn = (float) ($item->qty_in ?? 0);
            $qtyOut = (float) ($item->qty_out ?? 0);
            $isProduct = false;

            // نفس المنطق المستخدم في التعديل
            if ($qtyIn > 0 && $qtyOut == 0) {
                $isProduct = true;
            } elseif ($qtyOut > 0 && $qtyIn == 0) {
                $isProduct = false;
            } elseif ($item->detail_store == $this->invoice->acc2) {
                // Fallback: check if store matches product account
                // If accounts are same, check for additional cost percentage
                if ($this->invoice->acc1 == $this->invoice->acc2) {
                    if (($item->additional ?? 0) > 0) {
                        $isProduct = true;
                    }
                } else {
                    $isProduct = true;
                }
            }

            if ($isProduct) {
                $this->products->push([
                    'name' => $item->item->name ?? '-',
                    'quantity' => $item->fat_quantity ?? $item->qty_in ?? 0,
                    'unit_cost' => $item->fat_price ?? $item->cost_price ?? 0,
                    'cost_percentage' => $item->additional ?? 0,
                    'total_cost' => $item->detail_value ?? 0,
                ]);
            } else {
                // Get the display unit (fat_unit_id) instead of base unit (unit_id)
                $unitName = $this->getDisplayUnitName($item);

                $this->rawMaterials->push([
                    'name' => $item->item->name ?? '-',
                    'quantity' => $item->fat_quantity ?? $item->qty_out ?? 0,
                    'unit_name' => $unitName,
                    'unit_cost' => $item->fat_price ?? $item->cost_price ?? 0,
                    'total_cost' => $item->detail_value ?? 0,
                ]);
            }
        }

        // تحميل المصروفات
        $this->expenses = Expense::where('op_id', $this->invoice->id)
            ->with('account')
            ->get()
            ->map(function ($expense) {
                $description = str_replace('مصروف إضافي: ', '', $expense->description);
                $description = preg_replace('/ - فاتورة:.*$/', '', $description);

                return [
                    'description' => trim($description),
                    'account_name' => $expense->account->aname ?? '-',
                    'amount' => $expense->amount ?? 0,
                ];
            });

        // حساب الإجماليات
        $this->calculateTotals();
    }

    private function getDisplayUnitName($item)
    {
        // 1. Try to get unit from fat_unit_id (the unit the user selected)
        if ($item->fat_unit_id) {
            $displayUnit = \App\Models\Unit::find($item->fat_unit_id);
            if ($displayUnit) {
                return $displayUnit->name;
            }
        }

        // 2. If fat_unit_id not available, try to infer from quantities
        $qtyOut = (float) ($item->qty_out ?? 0);
        $fatQty = (float) ($item->fat_quantity ?? 0);

        if ($qtyOut > 0 && $fatQty > 0 && $item->item) {
            $ratio = $qtyOut / $fatQty;

            // Get all units for this item
            $units = $item->item->units()->get();

            // Find unit with matching u_val (conversion factor)
            foreach ($units as $unit) {
                $uVal = (float) ($unit->pivot->u_val ?? 0);
                if ($uVal > 0 && abs($uVal - $ratio) < 0.0001) {
                    return $unit->name;
                }
            }
        }

        // 3. Fallback to unit_id (base unit)
        if ($item->unit) {
            return $item->unit->name;
        }

        // 4. Last resort
        return '-';
    }

    private function calculateTotals()
    {
        $this->totals = [
            'products' => collect($this->products)->sum('total_cost'),
            'raw_materials' => collect($this->rawMaterials)->sum('total_cost'),
            'expenses' => collect($this->expenses)->sum('amount'),
            'manufacturing_cost' => 0,
        ];

        $this->totals['manufacturing_cost'] =
            $this->totals['raw_materials'] + $this->totals['expenses'];
    }

    public function printInvoice()
    {
        $this->dispatch('print-invoice');
    }

    public function render()
    {
        return view('manufacturing::livewire.manufacturing-show');
    }
}
