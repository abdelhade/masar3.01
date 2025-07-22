<?php

namespace Modules\Authorization\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authorization\Models\Role;
use Modules\Authorization\Models\Permission;

class RoleController extends Controller
{
public function __construct()
    {

        $this->middleware('can:عرض الادوار')->only(['index', 'show']);
        $this->middleware('can:إضافة الادوار')->only(['create', 'store']);
        $this->middleware('can:تعديل الادوار')->only(['edit', 'update']);
        $this->middleware('can:حذف الادوار')->only(['destroy']);
    }

    public function index()
    {
        $roles = Role::withCount('permissions')->paginate(10);
        return view('authorization::roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('category');
        return view('authorization::roles.create', compact('permissions'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create($request->only('name'));

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy('category');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('authorization::roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update($request->only('name'));
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated');
    }

    public function destroy(Role $role)
    {
        // if ($role->name === 'admin') {
        //     return redirect()->route('roles.index')
        //         ->with('error', 'لا يمكن حذف دور الأدمن');
        // }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted');
    }
}
