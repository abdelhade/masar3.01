<?php

namespace Modules\Rentals\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{JournalDetail, JournalHead, OperHead};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Modules\Accounts\Models\AccHead;
use Modules\Rentals\Http\Requests\RentalsLeaseRequest;
use Modules\Rentals\Models\{RentalsLease, RentalsUnit};
use RealRashid\SweetAlert\Facades\Alert;

class RentalsLeaseController extends Controller
{
    public function index()
    {
        $leases = RentalsLease::with('unit', 'client')->paginate(20);

        return view('rentals::leases.index', compact('leases'));
    }

    public function create(Request $request)
    {
        $selectedUnitId = $request->query('unit_id');
        $paymantAccount = AccHead::where('code', 'like', '42%')->where('is_basic', 0)->get();
        // جلب الوحدات مع تمييز النوع
        $units = RentalsUnit::with('item')->get()->mapWithKeys(function ($unit) {
            $name = ($unit->unit_type === 'item' && $unit->item) ? $unit->item->name : $unit->name;
            $label = ($unit->unit_type === 'item') 
                ? "[صنف] " . $name 
                : "[عقار] " . $name;
            return [$unit->id => $label];
        });

        return view('rentals::leases.create', compact('units', 'paymantAccount', 'selectedUnitId'));
    }

    public function store(RentalsLeaseRequest $request)
    {
        DB::beginTransaction();
        try {
            $lease = RentalsLease::create($request->validated());
            $unit = $lease->unit;

            // تحديد حساب الإيراد بناءً على نوع الموجر
            $acc2 = $request->acc_id;
            if ($unit->unit_type === 'item') {
                $itemAcc = AccHead::where('aname', 'like', '%تأجير أصناف%')->first();
                if ($itemAcc) $acc2 = $itemAcc->id;
            } else {
                $buildingAcc = AccHead::where('aname', 'like', '%إيراد عقارات%')->first();
                if ($buildingAcc) $acc2 = $buildingAcc->id;
            }

            $unit_info = ($unit->unit_type === 'item') 
                ? ' (صنف: ' . ($unit->name) . ')' 
                : ' (وحدة: ' . $unit->name . ')';

            $oper = OperHead::create([
                'pro_type' => 64,
                'pro_id' => $lease->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pro_date' => now()->toDateString(),
                'info' => 'عقد إيجار ' . $unit_info . ' للعميل #' . $request->client_id,
                'pro_value' => $request->rent_amount,
                'fat_net' => $request->rent_amount,
                'acc1' => $request->client_id ?? 0,
                'acc2' => $acc2,
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
            Alert::toast(__('Lease contract saved successfully'), 'success');

            return redirect()->route('rentals.leases.index');
        } catch (\Exception) {
            DB::rollBack();
            Alert::toast(__('An error occurred while saving the contract'), 'error');

            return back()->withInput();
        }
    }

    public function show($id)
    {
        $lease = RentalsLease::with(['unit.building', 'client', 'account'])->findOrFail($id);

        return view('rentals::leases.show', compact('lease'));
    }

    public function edit($id)
    {
        $lease = RentalsLease::with('client')->findOrFail($id);
        $paymantAccount = AccHead::where('code', 'like', '42%')->where('is_basic', 0)->get();
        // جلب الوحدات مع تمييز النوع
        $units = RentalsUnit::with('item')->get()->mapWithKeys(function ($unit) {
            $name = ($unit->unit_type === 'item' && $unit->item) ? $unit->item->name : $unit->name;
            $label = ($unit->unit_type === 'item') 
                ? "[صنف] " . $name 
                : "[عقار] " . $name;
            return [$unit->id => $label];
        });

        return view('rentals::leases.edit', compact('lease', 'units', 'paymantAccount'));
    }

    public function update(RentalsLeaseRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $lease = RentalsLease::findOrFail($id);
            $lease->update($request->validated());

            $oper = OperHead::where('pro_type', 64)->where('pro_id', $lease->id)->first();

            if ($oper) {
                $unit = $lease->unit;
                $acc2 = $request->acc_id;
                if ($unit->unit_type === 'item') {
                    $itemAcc = AccHead::where('aname', 'like', '%تأجير أصناف%')->first();
                    if ($itemAcc) $acc2 = $itemAcc->id;
                } else {
                    $buildingAcc = AccHead::where('aname', 'like', '%إيراد عقارات%')->first();
                    if ($buildingAcc) $acc2 = $buildingAcc->id;
                }

                $unit_info = ($unit->unit_type === 'item') 
                    ? ' (صنف: ' . ($unit->name) . ')' 
                    : ' (وحدة: ' . $unit->name . ')';

                $oper->update([
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'pro_date' => now()->toDateString(),
                    'info' => 'تعديل عقد إيجار ' . $unit_info . ' للعميل #' . $request->client_id,
                    'pro_value' => $request->rent_amount,
                    'fat_net' => $request->rent_amount,
                    'acc1' => $request->client_id ?? 0,
                    'acc2' => $acc2,
                    'user' => Auth::id(),
                ]);

                // Only update journal entries if $oper exists
                $journalHead = JournalHead::where('op_id', $oper->id)->where('pro_type', 64)->first();

                if ($journalHead) {
                    $journalHead->update([
                        'total' => $oper->pro_value,
                        'date' => $oper->pro_date,
                        'details' => $oper->info,
                        'user' => Auth::id(),
                    ]);

                    // Delete old journal details and create new ones
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
                        'debit' => $oper->pro_value,
                        'credit' => 0,
                        'type' => 1,
                        'info' => $oper->info,
                        'op_id' => $oper->id,
                    ]);
                }
            }

            DB::commit();
            Alert::toast(__('Lease contract updated successfully'), 'success');

            return redirect()->route('rentals.leases.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::toast(__('An error occurred while updating the contract'), 'error');

            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $lease = RentalsLease::findOrFail($id);
            $lease->delete();
            Alert::toast(__('Lease deleted successfully'), 'success');

            return redirect()->route('rentals.leases.index');
        } catch (Exception) {
            Alert::toast(__('An error occurred while deleting the contract'), 'error');

            return redirect()->back();
        }
    }
}
