<?php

namespace Modules\Progress\Http\Controllers;

use Modules\Progress\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct()
{
    $this->middleware('can:view progress-employees')->only('index');
    $this->middleware('can:create progress-employees')->only(['create', 'store']);
    $this->middleware('can:edit progress-employees')->only(['edit', 'update']);
    $this->middleware('can:delete progress-employees')->only('destroy');
}
    public function index()
    {
        $employees = Employee::latest()->get();
        return view('progress::employees.index', compact('employees'));
    }

    public function create()
    {
        $projects = \Modules\Progress\Models\Project::all();
        return view('progress::employees.create', compact('projects'));
    }


public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'phone'    => 'required|string|max:20',
        'email'    => [
            'nullable',
            'email',
            Rule::unique('employees', 'email')->whereNull('deleted_at'),
            Rule::unique('users', 'email'),
        ],
        'password' => 'required|string|min:6|confirmed', // إضافة validation لكلمة المرور
    ]);

    // هل الموظف موجود حتى لو متشال soft delete ؟
    $employee = Employee::withTrashed()->where('email', $request->email)->first();

    if ($employee && $employee->trashed()) {
        // رجّع الموظف
        $employee->restore();
        $employee->update([
            'name'     => $request->name,
            'position' => $request->position,
            'phone'    => $request->phone,
        ]);

        // أضف يوزر جديد (لأن القديم اتمسح نهائي)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password), // استخدام كلمة المرور من الطلب
        ]);

        // اربط الموظف باليوزر الجديد
        $employee->update(['user_id' => $user->id]);

    } elseif (!$employee) {
        // لو مفيش موظف خالص → أنشئ موظف ويوزر جديد
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password), // استخدام كلمة المرور من الطلب
        ]);

        Employee::create([
            'name'     => $request->name,
            'position' => $request->position,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'user_id'  => $user->id,
        ]);
    } else {
        return redirect()->back()->withErrors([
            'email' => 'البريد الإلكتروني مستخدم بالفعل.',
        ]);
    }

    return redirect()->route('employees.index')
        ->with('success', 'User added successfully');
}
    public function show(Employee $employee)
    {
        return view('progress::employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $projects = \Modules\Progress\Models\Project::all();
        return view('progress::employees.edit', compact('employee', 'projects'));
    }
public function update(Request $request, Employee $employee)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'phone'    => 'required|string|max:20',
        'email'    => 'nullable|email|unique:employees,email,' . $employee->id . '|unique:users,email,' . $employee->user_id,
        'password' => 'nullable|string|min:6|confirmed', // كلمة المرور اختيارية عند التحديث
    ]);

    // تحديث بيانات الموظف
    $employee->update([
        'name'     => $request->name,
        'position' => $request->position,
        'phone'    => $request->phone,
        'email'    => $request->email,
    ]);

    // لو عنده مستخدم مربوط نعدّله برضه
    if ($employee->user) {
        $userData = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // لو تم إدخال كلمة مرور جديدة
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }

        $employee->user->update($userData);
    }

    return redirect()->route('employees.index')
        ->with('success', 'تم تحديث بيانات الموظف بنجاح');
}

public function destroy(Employee $employee)
{
    // حذف السوفت ديليت للموظف
    $employee->delete();

    // لو الموظف مرتبط بيوزر نحذفه حذف نهائي
    if ($employee->user) {
        $employee->user()->forceDelete();
    }

    return redirect()->route('employees.index')
        ->with('success', 'user deleted successfully');
}



    public function editpermissions($id)
{
    $employee = User::findOrFail($id);

    // هنا نرجع نفس الـ Array اللي في الـ Seeder
      $permissionsByCategory = [
            'users' => ['list', 'create', 'edit', 'delete'],
            'projects' => ['list', 'create', 'edit', 'delete'],
            'employees' => ['list', 'create', 'edit', 'delete'],
            'dailyprogress' => ['list', 'create', 'edit', 'delete'],
        ];

    return view('progress::employees.permissions', compact('employee', 'permissionsByCategory'));
}

public function updatePermissions(Request $request, $id)
{
    $employee = User::findOrFail($id);
    $employee->syncPermissions($request->permissions ?? []);
    return redirect()->route('employees.index')->with('success', 'تم تحديث الصلاحيات بنجاح');
}
}
