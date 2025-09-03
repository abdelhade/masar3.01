<?php

namespace Modules\POS\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{OperHead, JournalHead, AccHead, Employee, Item, JournalDetail};
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    /**
     * عرض واجهة POS الرئيسية
     */
    public function index()
    {
        // التحقق من صلاحية الوصول لنظام POS
        if (!auth()->check() || !auth()->user()->can('استخدام نظام نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لاستخدام نظام نقاط البيع.');
        }

        // جلب المعاملات الأخيرة لهذا المستخدم (اختياري)
        $recentTransactions = OperHead::with(['acc1Head', 'acc2Head', 'employee'])
            ->where('pro_type', 10) // فواتير مبيعات فقط
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('pos::index', compact('recentTransactions'));
    }

    /**
     * إنشاء معاملة POS جديدة
     */
    public function create()
    {
        // التحقق من صلاحية إنشاء معاملات POS
        if (!auth()->check() || !auth()->user()->can('إنشاء معاملة نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لإنشاء معاملات نقاط البيع.');
        }

        // تمرير نوع المعاملة (10 = فاتورة مبيعات) مع hash للأمان
        $type = 10;
        $hash = md5($type);

        return view('pos::create', [
            'type' => $type,
            'hash' => $hash,
        ]);
    }

    /**
     * عرض معاملة POS محددة
     */
    public function show($id)
    {
        $transaction = OperHead::with(['operationItems.item', 'acc1Head', 'acc2Head', 'employee'])
            ->where('pro_type', 10) // فواتير مبيعات فقط
            ->findOrFail($id);

        // التحقق من الصلاحية
        if (!auth()->check() || !auth()->user()->can('عرض معاملة نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لعرض معاملات نقاط البيع.');
        }

        return view('pos::show', compact('transaction'));
    }

    /**
     * طباعة فاتورة POS
     */
    public function print($operation_id)
    {
        $operation = OperHead::with('operationItems.item')->findOrFail($operation_id);

        // التحقق من أن هذه معاملة POS (فاتورة مبيعات)
        if ($operation->pro_type !== 10) {
            abort(404, 'المعاملة المطلوبة غير موجودة.');
        }

        // التحقق من الصلاحية
        if (!auth()->check() || !auth()->user()->can('طباعة فاتورة نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لطباعة فواتير نقاط البيع.');
        }

        $acc1List = AccHead::where('id', $operation->acc1)->get();
        $acc2List = AccHead::where('id', $operation->acc2)->get();
        $employees = Employee::where('id', $operation->emp_id)->get();
        $items = Item::whereIn('id', $operation->operationItems->pluck('item_id'))->get();

        return view('pos::print', [
            'pro_id' => $operation->pro_id,
            'pro_date' => $operation->pro_date,
            'accural_date' => $operation->accural_date,
            'serial_number' => $operation->pro_serial,
            'acc1_id' => $operation->acc1,
            'acc2_id' => $operation->acc2,
            'emp_id' => $operation->emp_id,
            'type' => $operation->pro_type,
            'acc1List' => $acc1List,
            'acc2List' => $acc2List,
            'employees' => $employees,
            'items' => $items,
            'invoiceItems' => $operation->operationItems->map(function ($item) {
                $unit = \App\Models\Unit::find($item->unit_id);
                return [
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $item->qty_out, // في POS نستخدم qty_out للمبيعات
                    'price' => $item->item_price,
                    'discount' => $item->item_discount,
                    'sub_value' => $item->detail_value,
                    'available_units' => collect([$unit]),
                ];
            })->toArray(),
            'subtotal' => $operation->fat_total,
            'discount_percentage' => $operation->fat_disc_per,
            'discount_value' => $operation->fat_disc,
            'additional_percentage' => $operation->fat_plus_per,
            'additional_value' => $operation->fat_plus,
            'total_after_additional' => $operation->fat_net,
            'received_from_client' => $operation->paid_from_client,
            'notes' => $operation->info,
        ]);
    }

    /**
     * حذف معاملة POS
     */
    public function destroy($id)
    {
        $operation = OperHead::findOrFail($id);

        // التحقق من أن هذه معاملة POS (فاتورة مبيعات)
        if ($operation->pro_type !== 10) {
            abort(404, 'المعاملة المطلوبة غير موجودة.');
        }

        // التحقق من الصلاحية
        if (!auth()->check() || !auth()->user()->can('حذف معاملة نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لحذف معاملات نقاط البيع.');
        }

        try {
            // حذف جميع العناصر المرتبطة من operation_items
            $operation->operationItems()->delete();

            // حذف قيود اليومية المرتبطة بـ op_id
            JournalDetail::where('op_id', $operation->id)->delete();
            JournalHead::where('op_id', $operation->id)->orWhere('op2', $operation->id)->delete();

            // حذف أي سند آلي مرتبط بـ op2
            $autoVoucher = OperHead::where('op2', $operation->id)->where('is_journal', 1)->where('is_stock', 0)->first();
            if ($autoVoucher) {
                // حذف قيوده اليومية
                JournalDetail::where('op_id', $autoVoucher->id)->delete();
                JournalHead::where('op_id', $autoVoucher->id)->orWhere('op2', $autoVoucher->id)->delete();
                // حذف السند نفسه
                $autoVoucher->delete();
            }

            // حذف المعاملة نفسها
            $operation->delete();

            Alert::toast('تم حذف المعاملة وسنداتها بنجاح.', 'success');
            return redirect()->route('pos.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء حذف المعاملة: ' . $e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * تقارير POS
     */
    public function reports()
    {
        // التحقق من الصلاحية
        if (!auth()->check() || !auth()->user()->can('عرض تقارير نقاط البيع')) {
            abort(403, 'ليس لديك صلاحية لعرض تقارير نقاط البيع.');
        }

        // إحصائيات اليوم
        $todayStats = [
            'total_sales' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->sum('fat_net'),
            'transactions_count' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->count(),
            'items_sold' => OperHead::where('pro_type', 10)
                ->whereDate('created_at', today())
                ->withSum('operationItems', 'qty_out')
                ->get()
                ->sum('operation_items_sum_qty_out') ?? 0,
        ];

        return view('pos::reports', compact('todayStats'));
    }
}