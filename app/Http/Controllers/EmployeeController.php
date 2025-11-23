<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Hr-Employees')->only(['index']);
        // $this->middleware('can:view Employees Details')->only(['show']);
        $this->middleware('can:create Hr-Employees')->only(['create', 'store']);
        $this->middleware('can:edit Hr-Employees')->only(['edit', 'update']);
        $this->middleware('can:delete Hr-Employees')->only(['destroy']);
    }

    public function index()
    {
        return view('hr-management.employees.manage-employee');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
