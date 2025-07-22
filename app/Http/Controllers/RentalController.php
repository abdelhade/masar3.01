<?php

namespace App\Http\Controllers;

use App\Models\AccHead;
use App\Models\CostCenter;
use App\Models\Project;
use App\Models\Rental;
use App\Models\OperHead;
use App\Models\JournalHead;
use App\Models\JournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = OperHead::where('pro_type', '62')->get();
        return view('rentals.index', compact('rentals'));
    }

    public function create()
    {
        // جلب الحسابات المتاحة للتأجير
        $equipments = AccHead::where('rentable', 1)
            ->get();

        // جلب المشاريع
        $projects = Project::all();

        $customers = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '122%')
            ->get();

        $cost_centers = CostCenter::all();

        // جلب الموظفين
        $employees = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->get();


        return view('rentals.create', compact('cost_centers', 'equipments', 'projects', 'customers', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'acc3' => 'required|exists:acc_head,id',
            'acc1' => 'required|exists:acc_head,id',
            'acc2' => 'required|exists:acc_head,id',
            'rental_price' => 'required|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'details' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // إنشاء سجل التأجير
            $pro_type = 62;
            $lastProId = OperHead::where('pro_type', $pro_type)->max('pro_id');
            $newProId = $lastProId ? $lastProId + 1 : 1;

            $equipment = AccHead::findOrFail($request->acc3);
            $equipment->update([
                'rent_to' => $request->project_id,
        
            ]);
            $operhead = OperHead::create([
                'pro_id' => $newProId,
                'is_journal' => 1,
                'acc1' => $request->acc1,
                'acc2' => $request->acc2,
                'acc3' => $request->acc3,
                'details' => $request->details,
                'pro_date' => $request->pro_date,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'emp_id' => $request->emp_id,
                'pro_value' => $request->rental_price,
                'project_id' => $request->project_id,
                'cost_center' => $request->cost_center,
                'user' => Auth::id(),
                'pro_type' => $pro_type,
            ]);


            // إنشاء رأس اليومية
            $journalHead = JournalHead::create([
                'journal_id' => JournalHead::max('journal_id') + 1,
                'date' => $request->start_date,
                'total' => $request->rental_price,
                'details' => 'إيجار معدة #' . $request->equipment_id,
                'op_id' => $operhead->id,
                'pro_type' => $pro_type,
                'user' => Auth::id(),
            ]);


            // الطرف المدين: العميل
            JournalDetail::create([
                'journal_id' => $journalHead->id,
                'account_id' => $request->acc1,
                'debit' => $request->rental_price,
                'credit' => 0,
                'op_id' => $operhead->id,
                'type' => 0,
                'info' => 'إيجار معدة',
            ]);

            // الطرف الدائن: حساب المعدات
            JournalDetail::create([
                'journal_id' => $journalHead->id,
                'account_id' => $request->acc2,
                'debit' => 0,
                'credit' => $request->rental_price,
                'op_id' => $operhead->id,
                'type' => 1,
                'info' => 'إيجار معدة',
            ]);

            DB::commit();
            return redirect()->route('rentals.index')->with('success', 'تم حفظ عملية التأجير بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $rental = OperHead::findOrFail($id);

        $equipments = AccHead::where('rentable', 1)->get();

        $projects = Project::all();

        $customers = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '122%')
            ->get();

        $employees = AccHead::where('isdeleted', 0)
            ->where('is_basic', 0)
            ->where('code', 'like', '213%')
            ->get();

        $cost_centers = CostCenter::all();

        return view('rentals.edit', compact(
            'rental',
            'equipments',
            'projects',
            'customers',
            'employees',
            'cost_centers'
        ));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'acc3' => 'required|exists:acc_head,id|unique:acc_head,rent_to,' . $id,

            'acc1' => 'required|exists:acc_head,id',
            'acc2' => 'required|exists:acc_head,id',
            'rental_price' => 'required|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'details' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $equipment = AccHead::findOrFail($request->acc3);
            $equipment->update([
                'rent_to' => $request->project_id,
        
            ]);
            $rental = OperHead::findOrFail($id);

            // تحديث بيانات التأجير
            $rental->update([
                'acc1' => $request->acc1,
                'acc2' => $request->acc2,
                'acc3' => $request->acc3,
                'details' => $request->details,
                'pro_date' => $request->pro_date ?? now(),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'emp_id' => $request->emp_id,
                'pro_value' => $request->rental_price,
                'project_id' => $request->project_id,
                'cost_center' => $request->cost_center_id,
                'user' => Auth::id(),
            ]);

            // تحديث رأس اليومية
            $journalHead = JournalHead::where('op_id', $rental->id)->firstOrFail();
            $journalHead->update([
                'date' => $request->start_date,
                'total' => $request->rental_price,
                'details' => 'تعديل إيجار معدة #' . $request->acc3,
                'user' => Auth::id(),
            ]);

            // حذف التفاصيل القديمة
            JournalDetail::where('journal_id', $journalHead->id)->delete();

            // إنشاء التفاصيل من جديد
            JournalDetail::create([
                'journal_id' => $journalHead->id,
                'account_id' => $request->acc1,
                'debit' => $request->rental_price,
                'credit' => 0,
                'op_id' => $rental->id,
                'type' => 0,
                'info' => 'تعديل إيجار معدة',
            ]);

            JournalDetail::create([
                'journal_id' => $journalHead->id,
                'account_id' => $request->acc2,
                'debit' => 0,
                'credit' => $request->rental_price,
                'op_id' => $rental->id,
                'type' => 1,
                'info' => 'تعديل إيجار معدة',
            ]);

            DB::commit();
            return redirect()->route('rentals.index')->with('success', 'تم تحديث عملية التأجير بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()]);
        }
    }
    //show rental
    public function show($id)
    {
        $rental = OperHead::findOrFail($id);
        return view('rentals.show', compact('rental'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $rental = OperHead::findOrFail($id);

            // حذف تفاصيل اليومية
            JournalDetail::where('op_id', $rental->id)->delete();

            // حذف رأس اليومية
            JournalHead::where('op_id', $rental->id)->delete();

            // حذف سجل التأجير (OperHead)
            $rental->delete();

            DB::commit();
            return redirect()->route('rentals.index')->with('success', 'تم حذف عملية التأجير بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

}
