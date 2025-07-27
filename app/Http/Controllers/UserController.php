<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Authorization\Models\Role;
use App\Http\Requests\StoreUserRequest;
use Modules\Authorization\Models\Permission;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:عرض المدراء')->only(['index']);
        $this->middleware('can:إضافة المدراء')->only(['create', 'store']);
        $this->middleware('can:تعديل المدراء')->only(['update', 'edit']);
        $this->middleware('can:حذف المدراء')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('permissions')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        try {
            $roles = Role::pluck('name', 'id');
            $permissions = Permission::all()->groupBy('category');
            Alert::toast('تم إنشاء المستخدم بنجاح', 'error');

            return view('users.create', compact('roles', 'permissions'));
        } catch (\Exception $e) {
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
            Alert::toast('تم إنشاء المستخدم بنجاح', 'success');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء إنشاء المستخدم: ' . $e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('.', $perm->name)[0];
        });
        $userPermissions = $user->permissions->pluck('name')->toArray();

        return view('users.edit', compact('user', 'permissions', 'roles', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'password'    => 'nullable|confirmed|min:6',
            'permissions' => 'nullable|array',
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

            Alert::toast('تم تحديث المستخدم بنجاح', 'success');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء تحديث المستخدم: ' . $e->getMessage(), 'error');
            return redirect()->back()->withInput();
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            Alert::toast('تم حذف المستخدم بنجاح', 'success');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::toast('حدث خطأ أثناء حذف المستخدم: ' . $e->getMessage(), 'error');
            return redirect()->route('users.index');
        }
    }
}
