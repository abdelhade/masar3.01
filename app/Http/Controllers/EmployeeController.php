<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view Hr-Employees')->only(['index', 'show']);
        $this->middleware('can:create Hr-Employees')->only(['create', 'store']);
        $this->middleware('can:edit Hr-Employees')->only(['edit', 'update']);
        $this->middleware('can:delete Hr-Employees')->only(['destroy']);
    }

    /**
     * Display a listing of employees.
     */
    public function index(): View
    {
        return view('hr-management.employees.index');
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        return view('hr-management.employees.create');
    }

    /**
     * Store a newly created employee (handled by Livewire).
     */
    public function store(Request $request)
    {
        // Handled by Livewire component
        return redirect()->route('employees.index');
    }

    /**
     * Display the specified employee.
     */
    public function show(int $id): View
    {
        return view('hr-management.employees.show', ['employeeId' => $id]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(int $id): View
    {
        return view('hr-management.employees.edit', ['employeeId' => $id]);
    }

    /**
     * Update the specified employee (handled by Livewire).
     */
    public function update(Request $request, int $id)
    {
        // Handled by Livewire component
        return redirect()->route('employees.index');
    }

    /**
     * Remove the specified employee (handled by Livewire).
     */
    public function destroy(int $id)
    {
        // Handled by Livewire component
        return redirect()->route('employees.index');
    }
}
