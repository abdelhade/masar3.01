<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\Authorization\Models\Permission;
use Modules\Branches\Models\Branch;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Users')->only(['index', 'show']);
        $this->middleware('can:create Users')->only(['create', 'store']);
        $this->middleware('can:edit Users')->only(['update', 'edit']);
        $this->middleware('can:delete Users')->only(['destroy']);
    }

    public function index()
    {
        $users = User::with('permissions')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        try {
            $hasOptionType = Schema::hasColumn('permissions', 'option_type');

            if ($hasOptionType) {
                $permissions = Permission::where('option_type', '1')
                    ->get()
                    ->groupBy('category');
                $selectivePermissions = Permission::where('option_type', '2')
                    ->get()
                    ->groupBy('category');
            } else {
                $permissions = Permission::whereNotNull('category')
                    ->get()
                    ->groupBy('category');
                $selectivePermissions = collect();
            }

            $branches = Branch::where('is_active', 1)->get();

            return view('users.create', compact('permissions', 'selectivePermissions', 'branches'));
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء تحميل صفحة إنشاء المستخدم', 'error');

            return redirect()->route('users.index');
        }
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create($request->validated());
            if ($request->filled('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
                $user->givePermissionTo($permissions);
            }
            if ($request->filled('branches')) {
                $user->branches()->sync($request->branches);
            }
            Alert::toast('تم إنشاء المستخدم بنجاح', 'success');

            return redirect()->route('users.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء إنشاء المستخدم: ', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function show(User $user)
    {
        $user->load(['permissions', 'branches', 'roles']);
        $permissions = Permission::all()->groupBy('category');

        return view('users.show', compact('user', 'permissions'));
    }

    public function edit(User $user)
    {
        $hasOptionType = Schema::hasColumn('permissions', 'option_type');

        if ($hasOptionType) {
            $permissions = Permission::where('option_type', '1')
                ->whereNotNull('category')
                ->get()
                ->groupBy('category');

            $selectivePermissions = Permission::where('option_type', '2')
                ->get()
                ->groupBy('category');
        } else {
            $permissions = Permission::whereNotNull('category')
                ->get()
                ->groupBy('category');

            $selectivePermissions = collect();
        }

        $branches = Branch::where('is_active', 1)->get();

        // ✅ الطريقة الأولى (بدون eager loading)
        $userPermissions = $user->permissions->pluck('id')->toArray();
        $userBranches = $user->branches->pluck('id')->toArray();

        // أو الطريقة الثانية (مع query مباشر)
        // $userPermissions = $user->permissions()->pluck('id')->toArray();
        // $userBranches = $user->branches()->pluck('id')->toArray();

        return view('users.edit', compact(
            'user',
            'permissions',
            'userPermissions',
            'selectivePermissions',
            'branches',
            'userBranches'
        ));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
            'branches' => 'nullable|array',
            'branches.*' => 'integer|exists:branches,id',
        ]);

        try {
            $data = $request->only('name', 'email');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // تزامن الصلاحيات باستخدام IDs وتحويلها لأسماء
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)
                    ->pluck('name')
                    ->toArray();
                $user->syncPermissions($permissions);
            } else {
                $user->syncPermissions([]);
            }

            if ($request->filled('branches')) {
                $user->branches()->sync($request->branches);
            } else {
                $user->branches()->sync([]);
            }

            Alert::toast('تم تحديث المستخدم بنجاح', 'success');

            return redirect()->route('users.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء تحديث المستخدم: ', 'error');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            Alert::toast('تم حذف المستخدم بنجاح', 'success');

            return redirect()->route('users.index');
        } catch (\Exception) {
            Alert::toast('حدث خطأ أثناء حذف المستخدم: ', 'error');

            return redirect()->route('users.index');
        }
    }
}
