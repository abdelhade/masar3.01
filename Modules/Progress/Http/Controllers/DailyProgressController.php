<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\DailyProgress;
use Modules\Progress\Models\ProjectProgress as Project;
use Modules\Progress\Models\ProjectItem;
use Modules\Progress\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DailyProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view daily-progress')->only('index');
        $this->middleware('can:edit daily-progress')->only(['edit', 'update']);
        $this->middleware('can:delete daily-progress')->only('destroy');
    }

    /**
     * عرض قائمة التقدم اليومي مع الفلترة
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DailyProgress::with(['project', 'projectItem.dailyProgresses', 'projectItem.workItem.category', 'projectItem.subproject', 'employee']);

        // استبعاد المشاريع المسودة (draft)
        $query->whereHas('project', function ($q) {
            $q->where('is_draft', false);
        });

        // لو الموظف مش أدمن -> يقيد العرض على سجلاته هو فقط
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            // الحصول على employee_id من جدول employees
            $employee = Employee::where('user_id', $user->id)->first();
            
            if ($employee) {
                $query->where('employee_id', $employee->id);
                
                // جلب المشاريع المرتبطة بالموظف (غير المسودة)
                $projects = Project::where('is_draft', false)
                    ->whereHas('employees', function ($q) use ($employee) {
                        $q->where('employee_id', $employee->id);
                    })->select('id', 'name')->get();
            } else {
                // إذا لم يكن له سجل موظف، عرض قائمة فارغة
                $projects = collect([]);
                $query->whereRaw('1 = 0'); // لن يعرض أي نتائج
            }
        } else {
            // الأدمن والمدير يشوفوا كل المشاريع (غير المسودة)
            $projects = Project::where('is_draft', false)->select('id', 'name')->get();
        }

        // فلترة التاريخ (من - إلى)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // إذا تم تحديد من وإلى، فلترة بالنطاق الزمني
            $query->whereBetween('progress_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            // إذا تم تحديد من فقط
            $query->whereDate('progress_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            // إذا تم تحديد إلى فقط
            $query->whereDate('progress_date', '<=', $request->to_date);
        } elseif ($request->filled('progress_date')) {
            // دعم الحقل القديم للتوافق
            $query->whereDate('progress_date', $request->progress_date);
        } elseif (!$request->boolean('view_all')) {
            // إذا لم يتم تحديد أي تاريخ ولم يكن view_all، عرض اليوم فقط
            $query->whereDate('progress_date', today());
        }

        // فلترة المشروع
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // جلب البيانات وتجميعها حسب المشروع
        $allProgress = $query->orderBy('progress_date', 'desc')->get();
        
        // تجميع البيانات حسب المشروع ثم حسب subproject
        $groupedProgress = $allProgress->groupBy('project_id')->map(function ($items) {
            $firstItem = $items->first();
            $project = $firstItem->project ?? null;
            
            if (!$project) {
                return null;
            }
            
            // تجميع السجلات حسب subproject داخل المشروع
            $subprojectsGrouped = $items->groupBy(function ($item) {
                return $item->projectItem->subproject_name ?? 'بدون مشروع فرعي';
            })->map(function ($subItems, $subprojectName) {
                $firstSubItem = $subItems->first();
                $subproject = $firstSubItem->projectItem->subproject ?? null;
                $subprojectNameValue = $firstSubItem->projectItem->subproject_name ?? null;
                
                // تصفية السجلات للبنود القابلة للقياس فقط لحساب متوسط الإنجاز
                $measurableItems = $subItems->filter(function ($item) {
                    return $item->projectItem->is_measurable ?? false;
                });
                
                return [
                    'subproject' => $subproject,
                    'subproject_name' => $subprojectNameValue ?: $subprojectName,
                    'display_name' => $subproject ? $subproject->name : ($subprojectNameValue ?: $subprojectName),
                    'records' => $subItems,
                    'total_quantity' => $subItems->sum('quantity'),
                    'records_count' => $subItems->count(),
                    'latest_date' => $subItems->max('progress_date'),
                    'avg_completion' => $measurableItems->count() > 0 
                        ? round($measurableItems->avg('completion_percentage') ?: 0, 2)
                        : 0
                ];
            });
            
            // تصفية السجلات للبنود القابلة للقياس فقط لحساب متوسط الإنجاز على مستوى المشروع
            $measurableProjectItems = $items->filter(function ($item) {
                return $item->projectItem->is_measurable ?? false;
            });
            
            return [
                'project' => $project,
                'subprojects' => $subprojectsGrouped,
                'total_quantity' => $items->sum('quantity'),
                'records_count' => $items->count(),
                'latest_date' => $items->max('progress_date'),
                'avg_completion' => $measurableProjectItems->count() > 0
                    ? round($measurableProjectItems->avg('completion_percentage') ?: 0, 2)
                    : 0
            ];
        })->filter(function ($group) {
            // تصفية المجموعات التي لا تحتوي على مشروع (في حالة وجود بيانات غير متسقة)
            return $group !== null && $group['project'] !== null;
        });

        return view('progress::daily-progress.index', compact('groupedProgress', 'projects'));
    }


    public function create(Request $request)
    {
        $user = Auth::user();

        // جلب المشاريع النشطة وغير المحذوفة فقط
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            // الأدمن والمدير يشوفوا كل المشاريع النشطة
            $projects = Project::active()
                ->published()
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } else {
            // الحصول على employee_id من جدول employees
            $employee = Employee::where('user_id', $user->id)->first();
            
            if ($employee) {
                // جلب المشاريع النشطة المرتبطة بالموظف فقط
                $projects = Project::whereHas('employees', function ($query) use ($employee) {
                        $query->where('employee_id', $employee->id);
                    })
                    ->active()
                    ->published()
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
            } else {
                // إذا لم يكن له سجل موظف، عرض قائمة فارغة
                $projects = collect([]);
            }
        }

        // معاملات إضافية من URL (للربط مع مخطط جانت)
        $selectedProjectId = $request->get('project_id');
        $selectedItemId = $request->get('item_id');

        return view('progress::daily-progress.create', compact(
            'projects',
            'selectedProjectId',
            'selectedItemId'
        ));
    }

    /**
     * حفظ تقرير يومي جديد مع تحديث حالة المهام
     */
/**
 * حفظ تقرير يومي جديد مع تحديث حالة المهام
 */
public function store(Request $request)
{
    $validated = $request->validate([
        'project_id'   => 'required|exists:projects,id',
        'progress_date'=> 'required|date',
        'quantities'   => 'required|array',
        'quantities.*' => 'required|numeric|min:0',
        'notes'        => 'nullable|string'
    ]);

    // الحصول على employee_id من جدول employees بناءً على user_id
    $employee = Employee::where('user_id', Auth::id())->first();
    
    if (!$employee) {
        return back()
            ->withInput()
            ->withErrors(['error' => 'لا يوجد موظف مرتبط بحسابك. يرجى التواصل مع المسؤول.']);
    }
    
    $employeeId = $employee->id;

    try {
        DB::beginTransaction();

        $project = Project::findOrFail($validated['project_id']);
        $hasProgress = false;
        $warnings = []; // لتخزين التحذيرات

        foreach ($validated['quantities'] as $itemId => $qty) {
            if ($qty <= 0) continue;

            $item = ProjectItem::findOrFail($itemId);

            // حساب الكمية الحالية المنجزة والمتبقية
            $currentCompleted = $item->dailyProgresses->sum('quantity');
            $newCompleted = $currentCompleted + $qty;
            $remaining = $item->total_quantity - $currentCompleted;

            // إضافة تحذير إذا تجاوزت الكمية المخططة
            if ($qty > $remaining && $remaining > 0) {
                $warnings[] = "تحذير: الكمية المدخلة ({$qty}) تتجاوز الكمية المتبقية ({$remaining}) للبند: {$item->workItem->name}";
            }

            $completionPercentage = $item->total_quantity > 0
                ? round(($newCompleted / $item->total_quantity) * 100, 2)
                : 0;

            // إنشاء سجل التقدم
            DailyProgress::create([
                'project_id'            => $validated['project_id'],
                'project_item_id'       => $itemId,
                'progress_date'         => $validated['progress_date'],
                'quantity'              => $qty,
                'notes'                 => $validated['notes'] ?? null,
                'employee_id'           => $employeeId,
                'completion_percentage' => $completionPercentage
            ]);

            // تحديث بيانات البند
            $item->update([
                'completed_quantity'    => $newCompleted,
                'completion_percentage' => $completionPercentage
            ]);

            // تحديث حالة المهمة
            $this->updateTaskStatus($item);
            $hasProgress = true;
        }

        if (!$hasProgress) {
            throw new \Exception('يرجى إدخال كمية واحدة على الأقل');
        }

        // تحديث حالة المشروع ككل
        $this->updateProjectStatus($project->id);

        DB::commit();

        // Clear progress report cache after adding daily progress
        $this->clearProjectProgressCache($project->id);

        $successMessage = 'تم تسجيل التقدم اليومي بنجاح';

        // إضافة التحذيرات إلى رسالة النجاح
        if (!empty($warnings)) {
            $warningMessage = implode('<br>', $warnings);
            session()->flash('warning', $warningMessage);
        }

        // التوجيه حسب الصلاحيات
        if (Auth::user()->can('dailyprogress-list')) {
            return redirect()->route('progress.daily-progress.index')
                ->with('success', $successMessage);
        }

        return back()->with('success', $successMessage);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}

    /**
     * عرض تفاصيل تقرير يومي
     */
    public function show(DailyProgress $dailyProgress)
    {
        $dailyProgress->load(['project', 'projectItem.workItem', 'employee']);
        return view('progress::daily-progress.show', compact('dailyProgress'));
    }

    /**
     * فورم تعديل تقرير
     */
    public function edit(DailyProgress $dailyProgress)
    {
        $projects = Project::select('id', 'name')->get();
        $employees = Employee::select('id', 'name')->get();
        $projectItems = $dailyProgress->project
            ? $dailyProgress->project->projectItems()->with('workItem')->get()
            : collect();

        return view('progress::daily-progress.edit', compact(
            'dailyProgress',
            'projects',
            'employees',
            'projectItems'
        ));
    }

    /**
     * تحديث تقرير يومي
     */
/**
 * تحديث تقرير يومي
 */
public function update(Request $request, DailyProgress $dailyProgress)
{
    $validated = $request->validate([
        'quantity'      => 'required|numeric|min:0',
        'progress_date' => 'required|date',
        'notes'         => 'nullable|string'
    ]);

    try {
        DB::beginTransaction();

        $item = $dailyProgress->projectItem;
        $oldQuantity = $dailyProgress->quantity;
        $newQuantity = $validated['quantity'];

        // حساب الكمية الحالية المنجزة (بدون السجل الحالي)
        $currentCompleted = $item->dailyProgresses()
            ->where('id', '!=', $dailyProgress->id)
            ->sum('quantity');
        $newTotalCompleted = $currentCompleted + $newQuantity;

        // التحقق وإضافة تحذير إذا تجاوزت الكمية المخططة
        $warning = null;
        $remaining = $item->total_quantity - $currentCompleted;
        if ($newQuantity > $remaining && $remaining > 0) {
            $warning = "تحذير: الكمية المدخلة ({$newQuantity}) تتجاوز الكمية المتبقية ({$remaining}) للبند: {$item->workItem->name}";
        }

        $completionPercentage = $item->total_quantity > 0
            ? round(($newTotalCompleted / $item->total_quantity) * 100, 2)
            : 0;

        // تحديث السجل
        $dailyProgress->update(array_merge($validated, [
            'completion_percentage' => $completionPercentage
        ]));

        // تحديث بيانات البند
        $item->update([
            'completed_quantity'    => $newTotalCompleted,
            'completion_percentage' => $completionPercentage
        ]);

        // تحديث حالة المهمة والمشروع
        $this->updateTaskStatus($item);
        $this->updateProjectStatus($item->project_id);

        DB::commit();

        // Clear progress report cache after updating daily progress
        $this->clearProjectProgressCache($item->project_id);

        $successMessage = 'تم تحديث التقدم اليومي بنجاح';

        // إضافة التحذير إذا وُجد
        if ($warning) {
            session()->flash('warning', $warning);
        }

        return redirect()->route('progress.daily-progress.index')
            ->with('success', $successMessage);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}
    /**
     * حذف تقرير يومي
     */
    public function destroy(DailyProgress $dailyProgress)
    {
        try {
            DB::beginTransaction();

            $item = $dailyProgress->projectItem;
            $quantity = $dailyProgress->quantity;

            // حذف السجل
            $dailyProgress->delete();

            // إعادة حساب الكمية المكتملة
            $newCompleted = $item->dailyProgresses->sum('quantity');
            $completionPercentage = $item->total_quantity > 0
                ? round(($newCompleted / $item->total_quantity) * 100, 2)
                : 0;

            $item->update([
                'completed_quantity' => $newCompleted,
                'completion_percentage' => $completionPercentage
            ]);

            // تحديث حالة المهمة والمشروع
            $this->updateTaskStatus($item);
            $this->updateProjectStatus($item->project_id);

            DB::commit();

            // Clear progress report cache after deleting daily progress
            $this->clearProjectProgressCache($item->project_id);

            return redirect()->route('progress.daily-progress.index')
                ->with('success', 'تم حذف التسجيل اليومي بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطأ في حذف التسجيل']);
        }
    }

    /**
     * تحديث حالة المهمة بناءً على التقدم
     */
    private function updateTaskStatus(ProjectItem $item)
    {
        $item->refresh(); // تحديث البيانات من قاعدة البيانات

        $completionPercentage = $item->completion_percentage;

        if ($completionPercentage >= 100) {
            $status = 'completed';
        } elseif ($completionPercentage > 0) {
            $status = 'active';
        } else {
            $status = 'pending';
        }

        $item->update(['status' => $status]);
    }

    /**
     * تحديث حالة المشروع ككل
     */
    private function updateProjectStatus($projectId)
    {
        $project = Project::with('projectItems')->findOrFail($projectId);

        $totalItems = $project->projectItems->count();
        if ($totalItems == 0) return;

        $completedItems = $project->projectItems
            ->where('completion_percentage', '>=', 100)->count();
        $activeItems = $project->projectItems
            ->where('completion_percentage', '>', 0)
            ->where('completion_percentage', '<', 100)->count();

        // تحديث حالة المشروع
        if ($completedItems === $totalItems) {
            $project->update(['status' => 'completed']);
        } elseif ($activeItems > 0 || $completedItems > 0) {
            $project->update(['status' => 'active']);
        } else {
            $project->update(['status' => 'pending']);
        }
    }

    /**
     * Clear progress report cache for a project
     */
    private function clearProjectProgressCache($projectId)
    {
        $cacheKeys = Cache::get('project_progress_cache_keys', []);
        foreach ($cacheKeys as $key) {
            if (str_contains($key, "project_progress_{$projectId}_")) {
                Cache::forget($key);
            }
        }
    }
}
