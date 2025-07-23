<?php

namespace App\Http\Controllers;

use App\Models\OperHead;
use App\Models\JournalHead;
use Illuminate\Http\Request;
use App\Models\JournalDetail;
use Illuminate\Routing\Controller;
use RealRashid\SweetAlert\Facades\Alert;

class InvoiceController extends Controller
{

    public function __construct()
    {
        // فاتورة المبيعات
        $this->middleware('can:عرض فاتورة مبيعات')->only(['index', 'show' , 'create']);
        // $this->middleware('can:إضافة فاتورة مبيعات')->only(['create', 'store']);
        // $this->middleware('can:تعديل فاتورة مبيعات')->only(['edit', 'update']);
        // $this->middleware('can:حذف فاتورة مبيعات')->only(['destroy']);
        // $this->middleware('can:طباعة فاتورة مبيعات')->only(['print', 'pdf', 'export']);

        // مردود المبيعات
        $this->middleware('can:عرض مردود مبيعات')->only(['index', 'show' , 'create']);
        // $this->middleware('can:إضافة مردود مبيعات')->only(['returnsCreate', 'returnsStore']);
        // $this->middleware('can:تعديل مردود مبيعات')->only(['returnsEdit', 'returnsUpdate']);
        // $this->middleware('can:حذف مردود مبيعات')->only(['returnsDestroy']);
        // $this->middleware('can:طباعة مردود مبيعات')->only(['returnsPrint', 'returnsPdf']);

        // أمر بيع
        $this->middleware('can:عرض أمر بيع')->only(['index', 'show' , 'create']);
        // $this->middleware('can:إضافة أمر بيع')->only(['orderCreate', 'orderStore']);
        // $this->middleware('can:تعديل أمر بيع')->only(['orderEdit', 'orderUpdate']);
        // $this->middleware('can:حذف أمر بيع')->only(['orderDestroy']);
        // $this->middleware('can:طباعة أمر بيع')->only(['orderPrint']);

        // عرض سعر لعميل
        $this->middleware('can:عرض عرض سعر لعميل')->only(['index', 'show' , 'create']);
        // $this->middleware('can:إضافة عرض سعر لعميل')->only(['quotationCreate', 'quotationStore']);
        // $this->middleware('can:تعديل عرض سعر لعميل')->only(['quotationEdit', 'quotationUpdate']);
        // $this->middleware('can:حذف عرض سعر لعميل')->only(['quotationDestroy']);
        // $this->middleware('can:طباعة عرض سعر لعميل')->only(['quotationPrint']);

        // أمر حجز
        $this->middleware('can:عرض أمر حجز')->only(['index', 'show' , 'create']);
    //     $this->middleware('can:إضافة أمر حجز')->only(['reservationCreate', 'reservationStore']);
    //     $this->middleware('can:تعديل أمر حجز')->only(['reservationEdit', 'reservationUpdate']);
    //     $this->middleware('can:حذف أمر حجز')->only(['reservationDestroy']);
    //     $this->middleware('can:طباعة أمر حجز')->only(['reservationPrint']);
    }

    public function index()
    {
        $invoices = OperHead::with(['acc1Headuser', 'store', 'employee', 'acc1Head', 'acc2Head', 'type'])->get();

        return view('invoices.index', compact('invoices'));
    }


    public function create(Request $request)
    {
        return view('invoices.create', [
            'type' => $request->get('type'),
            'hash' => $request->get('q'),
        ]);

        // $type = (int) $request->get('type');
        // $hash = $request->get('q');

        // if ($hash !== md5($type)) {
        //     abort(403, 'Invalid type hash');
        // }
        // $lastProId = OperHead::max('pro_id');
        // $nextProId = $lastProId ? $lastProId + 1 : 1;

        // $clientsAccounts = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)
        //     ->where('code', 'like', '122%')
        //     ->select('id', 'aname')->get();
        // $suppliersAccounts = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)
        //     ->where('code', 'like', '211%')
        //     ->select('id', 'aname')->get();
        // $stores = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)->where('code', 'like', '123%')
        //     ->select('id', 'aname')->get();
        // $employees = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)->where('code', 'like', '213%')
        //     ->select('id', 'aname')->get();
        // $wasted = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)
        //     ->where('code', 'like', '441%')
        //     ->select('id', 'aname')->get();
        // $accounts = AccHead::where('isdeleted', 0)
        //     ->where('is_basic', 0)
        //     ->where('code', 'not like', '123%')
        //     ->select('id', 'aname')->get();
        // $map = [
        //     10 => ['acc1' => 'clientsAccounts', 'acc2' => 'stores'],
        //     11 => ['acc1' => 'stores', 'acc2' => 'suppliersAccounts'],
        //     12 => ['acc1' => 'stores', 'acc2' => 'clientsAccounts'],
        //     13 => ['acc1' => 'suppliersAccounts', 'acc2' => 'stores'],
        //     14 => ['acc1' => 'clientsAccounts', 'acc2' => 'stores'],
        //     15 => ['acc1' => 'stores', 'acc2' => 'suppliersAccounts'],
        //     16 => ['acc1' => 'clientsAccounts', 'acc2' => 'stores'],
        //     17 => ['acc1' => 'stores', 'acc2' => 'suppliersAccounts'],
        //     18 => ['acc1' => 'wasted', 'acc2' => 'stores'],
        //     19 => ['acc1' => 'accounts', 'acc2' => 'stores'],
        //     20 => ['acc1' => 'stores', 'acc2' => 'accounts'],
        //     21 => ['acc1' => 'stores', 'acc2' => 'stores'],
        //     22 => ['acc1' => 'clientsAccounts', 'acc2' => 'stores'],
        // ];

        // $acc1List = isset($map[$type]) ? ${$map[$type]['acc1']} : collect();

        // // force acc2List to always be stores
        // $acc2List = $stores;

        // return view('invoices.create', compact(
        //     'type',
        //     'acc1List',
        //     'acc2List',
        //     'clientsAccounts',
        //     'suppliersAccounts',
        //     'stores',
        //     'employees',
        //     'wasted',
        //     'nextProId'
        // ));
    }

    public function store(Request $request) {}

    public function show(string $id) {}



    public function edit(OperHead $invoice)
    {
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $operation = OperHead::findOrFail($id);
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
        // حذف العملية نفسها
        $operation->delete();
        Alert::toast('تم حذف العملية وسنداتها بنجاح.', 'success');
        return redirect()->back();
    }
}
