<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View
    {
        $departments = Department::orderBy('name')->get();

        $query = Employee::with('department');

        // Search functionality
        // Note: Expects 'search' query parameter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        // TODO (Intern Task): Implement department filtering
        // The view contains a department dropdown, but the selected department
        // is currently ignored by the query.
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        $employees = $query->latest()->paginate(10)->withQueryString();

        return view('employees.index', compact('employees', 'departments'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $departments = Department::orderBy('name')->get();

        return view('employees.create', compact('departments'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO (Intern Task): Add Form Request validation
        // Currently, no validation is performed before saving.
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'job_title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);
        $employee = Employee::create($request->all());

        // BUG: Notice the route name used for redirection below
        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee details.
     */
    public function show(Employee $employee): View
    {
        $employee->load('department');

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $departments = Department::orderBy('name')->get();

        // BUG: Check which view is being returned here
        return view('employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        // Populate attributes from the request
        $employee->fill($request->all());

        // BUG: Data is not persisting to the database!

        $employee->save();

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Update employee employment status.
     */
    public function updateStatus(Request $request, Employee $employee): RedirectResponse
    {
        // TODO (Intern Task): Validate status ('active', 'on_leave', 'inactive')
        // and update the employee's status in the database.

        $validated = $request->validate([
            'status' => 'required|in:active,on_leave,inactive',
        ]);

        $employee->status = $validated['status'];
        $employee->save();

        return redirect()->back()
            ->with('success', 'Employee updated successfully.');
    }
}
