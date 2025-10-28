<?php

namespace Modules\Invoices\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Invoices\Models\InvoiceTemplate;
use Modules\Invoices\Http\Requests\InvoiceTemplateRequest;

class InvoiceTemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceTemplate::with('invoiceTypes')
            ->orderBy('sort_order')
            ->paginate(15);

        return view('invoices::invoice-templates.index', compact('templates'));
    }

    public function create()
    {
        $availableColumns = InvoiceTemplate::availableColumns();
        $invoiceTypes = [
            10 => 'فاتورة مبيعات',
            11 => 'فاتورة مشتريات',
            12 => 'مردود مبيعات',
            13 => 'مردود مشتريات',
            14 => 'أمر بيع',
            15 => 'أمر شراء',
            16 => 'عرض سعر لعميل',
            17 => 'عرض سعر من مورد',
            18 => 'فاتورة توالف',
            19 => 'أمر صرف',
            20 => 'أمر إضافة',
            21 => 'تحويل من مخزن لمخزن',
            22 => 'أمر حجز',
            24 => 'فاتورة خدمة',
            25 => 'طلب احتياج',
        ];

        return view('invoices::invoice-templates.create', compact('availableColumns', 'invoiceTypes'));
    }

    public function store(InvoiceTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = InvoiceTemplate::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'visible_columns' => $validated['visible_columns'],
            'column_widths' => $validated['column_widths'] ?? [],
            'column_order' => $validated['column_order'] ?? [],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['invoice_types'] as $invoiceType) {
            $isDefault = isset($validated['is_default']) &&
                in_array($invoiceType, $validated['is_default']);

            $template->invoiceTypes()->create([
                'invoice_type' => $invoiceType,
                'is_default' => $isDefault,
            ]);
        }

        return redirect()->route('invoice-templates.index')
            ->with('success', 'تم إنشاء النموذج بنجاح');
    }


    public function edit(InvoiceTemplate $template)
    {
        $template->load('invoiceTypes');
        $availableColumns = InvoiceTemplate::availableColumns();
        $invoiceTypes = [
            10 => 'فاتورة مبيعات',
            11 => 'فاتورة مشتريات',
            12 => 'مردود مبيعات',
            13 => 'مردود مشتريات',
            14 => 'أمر بيع',
            15 => 'أمر شراء',
            16 => 'عرض سعر لعميل',
            17 => 'عرض سعر من مورد',
            18 => 'فاتورة توالف',
            19 => 'أمر صرف',
            20 => 'أمر إضافة',
            21 => 'تحويل من مخزن لمخزن',
            22 => 'أمر حجز',
            24 => 'فاتورة خدمة',
            25 => 'طلب احتياج',
        ];

        return view('invoices::invoice-templates.edit', compact('template', 'availableColumns', 'invoiceTypes'));
    }

    public function update(InvoiceTemplateRequest $request, InvoiceTemplate $template)
    {
        // dd($request->all());
        $validated = $request->validated();

        $template->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'visible_columns' => $validated['visible_columns'],
            'column_widths' => $validated['column_widths'] ?? [],
            'column_order' => $validated['column_order'] ?? [],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $template->invoiceTypes()->delete();

        foreach ($validated['invoice_types'] as $invoiceType) {
            $isDefault = isset($validated['is_default']) &&
                in_array($invoiceType, $validated['is_default']);

            $template->invoiceTypes()->create([
                'invoice_type' => $invoiceType,
                'is_default' => $isDefault,
            ]);
        }

        return redirect()->route('invoice-templates.index')
            ->with('success', 'تم تحديث النموذج بنجاح');
    }


    public function destroy(InvoiceTemplate $template)
    {
        $template->delete();

        return redirect()->route('invoice-templates.index')
            ->with('success', 'تم حذف النموذج بنجاح');
    }

    /**
     * تبديل حالة النموذج (نشط/غير نشط)
     */
    public function toggleActive(InvoiceTemplate $template)
    {
        $template->update([
            'is_active' => !$template->is_active
        ]);

        return back()->with('success', 'تم تحديث حالة النموذج');
    }
}
