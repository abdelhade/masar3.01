<?php

namespace Modules\Installments\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Installments\Models\{InstallmentPayment, InstallmentPlan};

class InstallmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Installment Plans')->only(['index', 'show']);
        $this->middleware('can:view Overdue Installments')->only(['overduePayments']);
        $this->middleware('can:create Installment Plans')->only(['create', 'store']);
        $this->middleware('can:edit Installment Plans')->only(['edit', 'update']);
        $this->middleware('can:delete Installment Plans')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index()
    {
        $installmentPlans = InstallmentPlan::with('client')->latest()->paginate(15); // افترض وجود علاقة client

        return view('installments::index', compact('installmentPlans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('installments::create');
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show($id)
    {
        $plan = InstallmentPlan::with('payments', 'client')->findOrFail($id);

        return view('installments::show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $plan = InstallmentPlan::with('payments', 'account')->findOrFail($id);

        return view('installments::edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $plan = InstallmentPlan::with('payments')->findOrFail($id);

            // Cancel all paid payments (delete their journal entries)
            $paidPayments = $plan->payments()->where('status', 'paid')->get();
            foreach ($paidPayments as $payment) {
                $this->deleteJournalEntry($payment, $plan);
            }

            // Delete all payments
            $plan->payments()->delete();

            // Delete the plan
            $plan->delete();

            DB::commit();

            return redirect()->route('installments.plans.index')
                ->with('success', 'تم حذف الخطة وجميع الأقساط والقيود المحاسبية بنجاح');
        } catch (\Exception) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الخطة: ');
        }
    }

    /**
     * Delete journal entry for a payment
     */
    private function deleteJournalEntry($payment, $plan)
    {
        // Find the OperHead (Receipt Voucher) for this payment
        $operHead = \App\Models\OperHead::where('pro_type', 32) // Receipt voucher type
            ->where('acc2', $plan->acc_head_id)
            ->where('details', 'like', "%قسط رقم {$payment->installment_number}%")
            ->where('details', 'like', "%خطة رقم {$plan->id}%")
            ->first();

        if ($operHead) {
            // Delete JournalDetails
            \App\Models\JournalDetail::where('op_id', $operHead->id)->delete();

            // Delete JournalHead
            \App\Models\JournalHead::where('op_id', $operHead->id)->delete();

            // Delete OperHead (Receipt Voucher)
            $operHead->delete();
        }
    }

    public function overduePayments()
    {
        // جلب كل الأقساط التي لم تدفع بعد وتاريخ استحقاقها قد فات
        $overduePayments = InstallmentPayment::with(['plan.client']) // جلب الخطة والعميل المرتبط بها
            ->where('status', '!=', 'paid') // التي لم تدفع
            ->where('due_date', '<', Carbon::now()) // وتاريخ استحقاقها في الماضي
            ->latest('due_date') // ترتيبها حسب الأقدم
            ->paginate(20); // تقسيم النتائج لصفحات

        return view('installments::overdue_payments', compact('overduePayments'));
    }
}
