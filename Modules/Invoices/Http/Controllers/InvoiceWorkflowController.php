<?php

namespace Modules\Invoices\Http\Controllers;

use App\Models\OperHead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceWorkflowController extends Controller
{
    public function index()
    {
        // list request orders (pro_type = 25)
        $requests = OperHead::where('pro_type', 25)
            ->orderBy('pro_date', 'desc')
            ->paginate(20);

        return view('invoices::invoices.requests-index', ['requests' => $requests]);
    }

    public function show(string $id)
    {
        // allow passing either primary id or pro_id
        $root = OperHead::find($id);
        if (! $root) {
            // try find by pro_id
            $root = OperHead::where('pro_id', $id)->first();
        }
        if (! $root) {
            abort(404);
        }

        // define stages and expected pro_type mapping (can be adjusted)
        $stageDefinitions = [
            ['name' => 'طلب احتياج', 'type' => 25, 'icon' => 'fa-list-check'],
            ['name' => 'عرض سعر من مورد', 'type' => 17, 'icon' => 'fa-file-invoice-dollar'],
            ['name' => 'أمر شراء', 'type' => 15, 'icon' => 'fa-shopping-cart'],
            ['name' => 'فاتورة شراء', 'type' => 11, 'icon' => 'fa-file-invoice'],
            ['name' => 'إذن صرف / تحويل مخزن', 'type' => 19, 'icon' => 'fa-warehouse'],
        ];

        $stages = [];

        foreach ($stageDefinitions as $index => $def) {
            $completed = false;
            $details = '';

            // first stage is the root itself
            if ($index === 0) {
                $completed = $root->pro_type == $def['type'];
                $date = $root->pro_date ? \Carbon\Carbon::parse($root->pro_date)->format('d/m/Y') : now()->format('d/m/Y');
                $details = $root->info ?: ("#{$root->id} - " . $date);
            } else {
                // find child operations that were created from this root (origin_id, parent_id or op2 linking)
                $child = OperHead::where(function ($q) use ($root) {
                    $q->where('origin_id', $root->id)
                        ->orWhere('parent_id', $root->id)
                        ->orWhere('op2', $root->id);
                })
                    ->where('pro_type', $def['type'])
                    ->orderBy('id', 'desc')
                    ->first();

                if ($child) {
                    $completed = true;
                    $date = $child->pro_date ? \Carbon\Carbon::parse($child->pro_date)->format('d/m/Y') : now()->format('d/m/Y');
                    $details = $child->info ?: ("#{$child->id} - " . $date);
                } else {
                    $details = 'لم يتم إنشاء هذه المرحلة بعد';
                }
            }

            $stages[] = [
                'name' => $def['name'],
                'status' => $completed ? 'completed' : 'pending',
                'id' => $completed ? ($index === 0 ? $root->id : $child->id) : null,
                'details' => $details,
                'icon' => $def['icon'],
                'workflow_state' => $completed ? ($index === 0 ? $root->workflow_state : $child->workflow_state) : null,
            ];
        }

        return view('invoices::invoices.track', ['stages' => $stages, 'root' => $root]);
    }

    public function search()
    {
        // Now search is just another way to get to index, which lists all request orders
        return $this->index();
    }

    public function confirm(Request $request, string $id)
    {
        $request->validate(['next_stage' => 'required|integer']);

        $root = OperHead::find($id);
        if (!$root) {
            $root = OperHead::where('pro_id', $id)->firstOrFail();
        }

        // ✅ تحديد المراحل بناءً على next_stage
        $stageMapping = [
            1 => ['type' => 25, 'state' => 1], // طلب احتياج
            2 => ['type' => 17, 'state' => 2], // عرض سعر
            3 => ['type' => 15, 'state' => 3], // أمر شراء
            4 => ['type' => 11, 'state' => 4], // فاتورة شراء
            5 => ['type' => 19, 'state' => 5]  // إذن صرف
        ];

        $nextStage = $request->input('next_stage');
        if (!isset($stageMapping[$nextStage])) {
            return back()->with('error', 'المرحلة المطلوبة غير صالحة');
        }

        $nextType = $stageMapping[$nextStage]['type'];
        $nextWorkflowState = $stageMapping[$nextStage]['state'];

        // ✅ التحقق من إذا كان التحويل ممكن
        $currentWorkflowState = $root->workflow_state ?? 0;
        if ($nextWorkflowState <= $currentWorkflowState) {
            return back()->with('error', 'لا يمكن الانتقال إلى مرحلة سابقة أو الحالية');
        }

        // التحقق إذا المرحلة موجودة مسبقاً كي لا ينشئها مرتين
        // $existingStage = OperHead::where(function($q) use ($root) {
        //     $q->where('origin_id', $root->id)
        //         ->orWhere('parent_id', $root->id)
        //         ->orWhere('op2', $root->id);
        // })->where('pro_type', $nextType)->first();

        // if ($existingStage) {
        //     return redirect()->route('invoices.edit', $existingStage->id)
        //         ->with('info', 'تم فتح المرحلة الموجودة مسبقاً.');
        // }

        // ✅ تحديث workflow_state للـ root
        $root->update([
            'workflow_state' => $nextWorkflowState,
            'is_locked' => 1
        ]);

        $hash = md5($nextType);

        // ✅ بيانات الـ redirect
        $redirectData = [
            'type' => $nextType,
            'q' => $hash,
            'acc1' => $root->acc1,
            'acc2' => $root->acc2,
            'emp_id' => $root->emp_id,
            'pro_value' => $root->pro_value,
            'fat_total' => $root->fat_total,
            'op2' => $root->id,
            'parent_id' => $root->id,
            'origin_id' => $root->origin_id ?: $root->id,
            'branch_id' => $root->branch_id,
            'source_pro_id' => $root->pro_id, // أضف هذه
            'info' => 'تم إنشاء من ' . $this->titles[$root->pro_type] . ' #' . $root->id,
        ];

        return redirect()->route('invoices.create', $redirectData)
            ->with('info', 'يرجى مراجعة البيانات قبل الحفظ');
    }

    // ✅ أضف array الـ titles كـ property في الـ Controller
    protected $titles = [
        25 => 'طلب احتياج',
        17 => 'عرض سعر من مورد',
        15 => 'أمر شراء',
        11 => 'فاتورة شراء',
        19 => 'إذن صرف'
    ];
}
