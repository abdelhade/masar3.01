<?php

namespace Modules\Progress\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function permissionsMatrix()
    {
        // جلب كل الصلاحيات
        $permissions = Permission::all();
        // جلب كل الأدوار
        $roles = Role::all();
        // تجميع الصلاحيات حسب الخاصية (users, projects, ...)
        $grouped = collect();
        foreach ($permissions as $permission) {
            $parts = explode('-', $permission->name, 2);
            $feature = $parts[0];
            $action = $parts[1] ?? '';
            $grouped[$feature][$action] = $permission->name;
        }
        return view('roles.permissions_matrix', [
            'roles' => $roles,
            'groupedPermissions' => $grouped
        ]);
    } 
       public function updatePermissions(Request $request)
    {
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            $perms = $request->input('permissions.' . $role->id, []);
            $role->syncPermissions($perms);
        }
        return redirect()->route('roles.permissions-matrix')->with('success', 'تم تحديث الصلاحيات بنجاح');
    }
}
