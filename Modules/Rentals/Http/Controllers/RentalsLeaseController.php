<?php

namespace Modules\Rentals\Http\Controllers;

use Exception;
use App\Models\{AccHead, OperHead, JournalHead, JournalDetail};
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Rentals\Http\Requests\RentalsLeaseRequest;
use Modules\Rentals\Models\{RentalsUnit, RentalsLease};

class RentalsLeaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leases = RentalsLease::with('unit', 'client')->paginate(20);
        return view('rentals::leases.index', compact('leases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paymantAccount = AccHead::where('rentable', 0)
            ->where('code', 'like', '%42002%')
            ->get();

        $units = RentalsUnit::pluck('name', 'id');

        $clients = AccHead::where('is_basic', 1)
            ->where('code', 'like', '%1103%')
            ->get();
        return view('rentals::leases.create', compact('units', 'clients', 'paymantAccount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RentalsLeaseRequest $request)
    {
        DB::beginTransaction();
        try {
            RentalsLease::create($request->validated());
            $operation_id = OperHead::max('id') + 1;
            $oper = OperHead::create([
                'pro_type' => 64,
                'pro_id' => $operation_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pro_date' => now()->toDateString(),
                'info' => 'عقد إيجار للوحدة #' . $request->unit_id . ' للعميل #' . $request->client_id,
                'pro_value' => $request->rent_amount,
                'fat_net' => $request->rent_amount,
                'acc1' => $request->client_id ?? 0,
                'acc2' => $request->acc_id,
                'user' => Auth::id(),
            ]);

            // 3️⃣ إنشاء JournalHead
            $journalId = JournalHead::max('journal_id') + 1;
            JournalHead::create([
                'journal_id' => $journalId,
                'total' => $oper->pro_value,
                'op_id' => $oper->id,
                'op2' => 0,
                'pro_type' => $oper->pro_type,
                'date' => $oper->pro_date,
                'details' => $oper->info,
                'user' => Auth::id(),
            ]);

            // 4️⃣ إنشاء JournalDetails
            // دائن: العميل
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $oper->acc1,
                'debit' => $oper->pro_value,
                'credit' => 0,
                'type' => 1,
                'info' => $oper->info,
                'op_id' => $oper->id,
            ]);

            // مدين: حساب الإيراد
            JournalDetail::create([
                'journal_id' => $journalId,
                'account_id' => $oper->acc2,
                'debit' => 0,
                'credit' => $oper->pro_value,
                'type' => 1,
                'info' => $oper->info,
                'op_id' => $oper->id,
            ]);

            DB::commit();
            Alert::toast('تم حفظ عقد الإيجار بنجاح', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('خطأ أثناء حفظ عقد الإيجار: ' . $e->getMessage());
            Alert::toast('حدث خطأ أثناء حفظ العقد', 'error');
            return back()->withInput();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('rentals::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $lease = RentalsLease::findOrFail($id);

        $paymantAccount = AccHead::where('rentable', 0)
            ->where('code', 'like', '%42002%')
            ->get();

        $units = RentalsUnit::pluck('name', 'id');

        $clients = AccHead::where('is_basic', 1)
            ->where('code', 'like', '%1103%')
            ->get();

        return view('rentals::leases.edit', compact('lease', 'units', 'clients', 'paymantAccount'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(RentalsLeaseRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $lease = RentalsLease::findOrFail($id);
            $lease->update($request->validated());

            $oper = OperHead::where('pro_type', 64)
                ->where('pro_id', $lease->id)
                ->first();

            if ($oper) {
                $oper->update([
                    'start_date' => $request->start_date,
                    'end_date'   => $request->end_date,
                    'pro_date'   => now()->toDateString(),
                    'info'       => 'تعديل عقد إيجار للوحدة #' . $request->unit_id . ' للعميل #' . $request->client_id,
                    'pro_value'  => $request->rent_amount,
                    'fat_net'    => $request->rent_amount,
                    'acc1'       => $request->client_id ?? 0,
                    'acc2'       => $request->acc_id,
                    'user'       => Auth::id(),
                ]);
            }
            $journalHead = JournalHead::where('op_id', $oper->id)
                ->where('pro_type', 64)
                ->first();

            if ($journalHead) {
                $journalHead->update([
                    'total'   => $oper->pro_value,
                    'date'    => $oper->pro_date,
                    'details' => $oper->info,
                    'user'    => Auth::id(),
                ]);
            }

            if ($journalHead) {
                JournalDetail::where('journal_id', $journalHead->journal_id)->delete();
                JournalDetail::create([
                    'journal_id' => $journalHead->journal_id,
                    'account_id' => $oper->acc1,
                    'debit' => 0,
                    'credit' => $oper->pro_value,
                    'type' => 1,
                    'info' => $oper->info,
                    'op_id' => $oper->id,
                ]);

                JournalDetail::create([
                    'journal_id' => $journalHead->journal_id,
                    'account_id' => $oper->acc2,
                    'debit' =>  $oper->pro_value,
                    'credit' => 0,
                    'type' => 1,
                    'info' => $oper->info,
                    'op_id' => $oper->id,
                ]);
            }

            DB::commit();
            Alert::toast('تم تعديل عقد الإيجار بنجاح', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('خطأ أثناء تعديل عقد الإيجار: ' . $e->getMessage());
            Alert::toast('حدث خطأ أثناء تعديل العقد', 'error');
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $lease = RentalsLease::findOrFail($id);
            $lease->delete();
            Alert::toast('تم حذف العقد بنجاح.', 'success');
            return redirect()->route('rentals.leases.index');
        } catch (Exception) {
            Alert::toast('حدث خطأ أثناء حذف العقد: ', 'error');
            return redirect()->back();
        }
    }
}
