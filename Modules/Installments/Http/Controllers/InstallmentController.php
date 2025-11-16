<?php

namespace Modules\Installments\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Installments\Models\InstallmentPlan;
use Modules\Installments\Models\InstallmentPayment;

class InstallmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $installmentPlans = InstallmentPlan::with('client')->latest()->paginate(15); // افترض وجود علاقة client
        return view('installments::index', compact('installmentPlans'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('installments::create');
    }


    /**
     * Show the specified resource.
     * @param int $id
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
        return view('installments::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

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
