<?php

namespace Modules\Services\Livewire;

use Livewire\Component;
use Modules\Services\Models\ServiceInvoice as ServiceInvoiceModel;
use Modules\Services\Models\ServiceInvoiceItem;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceUnit;
use App\Models\AccHead;
use Modules\Branches\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ServiceInvoiceForm extends Component
{
    public $invoice;
    public $type = 'sell';
    public $invoice_date;
    public $due_date;
    public $supplier_id;
    public $customer_id;
    public $branch_id;
    public $notes = '';
    public $terms_conditions = '';
    public $status = 'draft';

    // Invoice items
    public $items = [];
    public $newItem = [
        'service_id' => '',
        'service_unit_id' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'discount_percentage' => 0,
        'tax_percentage' => 0,
        'description' => '',
    ];

    // Calculated totals
    public $subtotal = 0;
    public $totalDiscount = 0;
    public $totalTax = 0;
    public $totalAmount = 0;

    protected $rules = [
        'type' => 'required|in:buy,sell',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'supplier_id' => 'required_if:type,buy|nullable|exists:acc_head,id',
        'customer_id' => 'required_if:type,sell|nullable|exists:acc_head,id',
        'branch_id' => 'nullable|exists:branches,id',
        'notes' => 'nullable|string',
        'terms_conditions' => 'nullable|string',
        'status' => 'required|in:draft,pending,approved,rejected,cancelled',
        'items' => 'required|array|min:1',
        'items.*.service_id' => 'required|exists:services,id',
        'items.*.quantity' => 'required|numeric|min:0.001',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
        'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
    ];

    protected $messages = [
        'type.required' => 'نوع الفاتورة مطلوب',
        'type.in' => 'نوع الفاتورة غير صحيح',
        'invoice_date.required' => 'تاريخ الفاتورة مطلوب',
        'invoice_date.date' => 'تاريخ الفاتورة غير صحيح',
        'due_date.date' => 'تاريخ الاستحقاق غير صحيح',
        'due_date.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ الفاتورة',
        'supplier_id.required_if' => 'المورد مطلوب لفاتورة الشراء',
        'supplier_id.exists' => 'المورد المحدد غير موجود',
        'customer_id.required_if' => 'العميل مطلوب لفاتورة البيع',
        'customer_id.exists' => 'العميل المحدد غير موجود',
        'branch_id.exists' => 'الفرع المحدد غير موجود',
        'status.required' => 'حالة الفاتورة مطلوبة',
        'status.in' => 'حالة الفاتورة غير صحيحة',
        'items.required' => 'يجب إضافة عنصر واحد على الأقل',
        'items.min' => 'يجب إضافة عنصر واحد على الأقل',
        'items.*.service_id.required' => 'الخدمة مطلوبة',
        'items.*.service_id.exists' => 'الخدمة المحددة غير موجودة',
        'items.*.quantity.required' => 'الكمية مطلوبة',
        'items.*.quantity.numeric' => 'الكمية يجب أن تكون رقماً',
        'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
        'items.*.unit_price.required' => 'سعر الوحدة مطلوب',
        'items.*.unit_price.numeric' => 'سعر الوحدة يجب أن يكون رقماً',
        'items.*.unit_price.min' => 'سعر الوحدة يجب أن يكون أكبر من أو يساوي صفر',
        'items.*.discount_percentage.numeric' => 'نسبة الخصم يجب أن تكون رقماً',
        'items.*.discount_percentage.min' => 'نسبة الخصم يجب أن تكون أكبر من أو تساوي صفر',
        'items.*.discount_percentage.max' => 'نسبة الخصم يجب أن تكون أقل من أو تساوي 100',
        'items.*.tax_percentage.numeric' => 'نسبة الضريبة يجب أن تكون رقماً',
        'items.*.tax_percentage.min' => 'نسبة الضريبة يجب أن تكون أكبر من أو تساوي صفر',
        'items.*.tax_percentage.max' => 'نسبة الضريبة يجب أن تكون أقل من أو تساوي 100',
    ];

    public function mount($invoiceId = null, $type = 'sell')
    {
        $this->type = $type;
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->branch_id = Auth::check() ? Auth::user()->branch_id : null;

        if ($invoiceId) {
            $this->invoice = ServiceInvoiceModel::with('items.service', 'items.serviceUnit')->findOrFail($invoiceId);
            $this->loadInvoiceData();
        } else {
            $this->addNewItem();
        }
    }

    public function loadInvoiceData()
    {
        $this->type = $this->invoice->type;
        $this->invoice_date = $this->invoice->invoice_date->format('Y-m-d');
        $this->due_date = $this->invoice->due_date?->format('Y-m-d');
        $this->supplier_id = $this->invoice->supplier_id;
        $this->customer_id = $this->invoice->customer_id;
        $this->branch_id = $this->invoice->branch_id;
        $this->notes = $this->invoice->notes;
        $this->terms_conditions = $this->invoice->terms_conditions;
        $this->status = $this->invoice->status;

        $this->items = $this->invoice->items()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'service_id' => $item->service_id,
                'service_unit_id' => $item->service_unit_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'tax_percentage' => $item->tax_percentage,
                'description' => $item->description,
            ];
        })->toArray();

        $this->calculateTotals();
    }

    public function addNewItem()
    {
        $this->items[] = [
            'service_id' => '',
            'service_unit_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_percentage' => 0,
            'description' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems($value, $key)
    {
        $this->calculateTotals();
    }

    public function updatedNewItem($value, $key)
    {
        if ($key === 'service_id' && $value) {
            $service = Service::find($value);
            if ($service) {
                $this->newItem['unit_price'] = $service->price;
                $this->newItem['service_unit_id'] = $service->service_unit_id;
            }
        }
    }

    public function addItemFromNew()
    {
        $this->validate([
            'newItem.service_id' => 'required|exists:services,id',
            'newItem.quantity' => 'required|numeric|min:0.001',
            'newItem.unit_price' => 'required|numeric|min:0',
        ]);

        $this->items[] = $this->newItem;
        $this->newItem = [
            'service_id' => '',
            'service_unit_id' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'discount_percentage' => 0,
            'tax_percentage' => 0,
            'description' => '',
        ];

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        $this->totalDiscount = 0;
        $this->totalTax = 0;

        foreach ($this->items as $item) {
            if (!empty($item['service_id']) && $item['quantity'] > 0 && $item['unit_price'] >= 0) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $discountAmount = ($lineSubtotal * ($item['discount_percentage'] ?? 0)) / 100;
                $discountedAmount = $lineSubtotal - $discountAmount;
                $taxAmount = ($discountedAmount * ($item['tax_percentage'] ?? 0)) / 100;

                $this->subtotal += $lineSubtotal;
                $this->totalDiscount += $discountAmount;
                $this->totalTax += $taxAmount;
            }
        }

        $this->totalAmount = $this->subtotal - $this->totalDiscount + $this->totalTax;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'type' => $this->type,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'supplier_id' => $this->supplier_id,
            'customer_id' => $this->customer_id,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->totalDiscount,
            'tax_amount' => $this->totalTax,
            'total_amount' => $this->totalAmount,
            'updated_by' => Auth::check() ? Auth::id() : null,
        ];

        if ($this->invoice) {
            $this->invoice->update($data);
            $invoice = $this->invoice;
        } else {
            $data['created_by'] = Auth::check() ? Auth::id() : null;
            $invoice = ServiceInvoiceModel::create($data);
        }

        // Update invoice items
        $invoice->items()->delete();
        foreach ($this->items as $item) {
            if (!empty($item['service_id'])) {
                $invoice->items()->create([
                    'service_id' => $item['service_id'],
                    'service_unit_id' => $item['service_unit_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'description' => $item['description'],
                ]);
            }
        }

        $this->dispatch('show-alert', [
            'type' => 'success',
            'message' => $this->invoice ? 'تم تحديث الفاتورة بنجاح' : 'تم إنشاء الفاتورة بنجاح'
        ]);

        return redirect()->route('services.invoices.show', $invoice->id);
    }

    public function render()
    {
        $services = Service::active()->with('serviceUnit')->get();
        $serviceUnits = ServiceUnit::active()->get();
        $suppliers = AccHead::where('type', 'supplier')->get();
        $customers = AccHead::where('type', 'customer')->get();
        $branches = Branch::active()->get();

        return view('services::livewire.service-invoice-form', [
            'services' => $services,
            'serviceUnits' => $serviceUnits,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'branches' => $branches,
        ]);
    }
}
