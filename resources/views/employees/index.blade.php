@extends('layouts.app')

@section('title', 'Employee Dashboard - EMS Portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0">Employee Directory</h2>
            <p class="text-muted mb-0">Manage company staff, review departments, and track employment status.</p>
        </div>
        <div>
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Add Employee
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="row g-3 align-items-center" id="filterForm">
                {{-- Search input --}}
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="searchInput" class="form-control"
                            placeholder="Search by name, email, or title..."
                            value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Department filter dropdown --}}
                <div class="col-md-4">
                    <select name="department_id" class="form-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter action buttons --}}
                <div class="col-md-3 d-flex gap-2">
                    {{-- Search button --}}
                    <button type="submit" class="btn btn-primary flex-grow-1" id="btnSearch">
                        <i class="bi bi-filter me-1"></i> Search
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Employees ({{ $employees->total() }})</h5>
            <span class="badge bg-light text-dark border">Page {{ $employees->currentPage() }} of
                {{ $employees->lastPage() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 70px;">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Department</th>
                            <th scope="col">Job Title</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end" style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td class="fw-semibold text-muted">#{{ $employee->id }}</td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $employee->name }}</span>
                                </td>
                                <td>{{ $employee->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $employee->department ? $employee->department->name : 'Unassigned' }}
                                    </span>
                                </td>
                                <td>{{ $employee->job_title }}</td>
                                <td>
                                    @if ($employee->status === 'active')
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @elseif ($employee->status === 'on_leave')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">On
                                            Leave</span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        {{-- View button (working) --}}
                                        <a href="{{ route('employees.show', $employee->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-eye"></i> View
                                        </a>

                                        {{-- Edit button (broken) --}}
                                        <a href="{{ route('employees.edit', $employee->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>

                                        {{-- Delete button (broken) --}}
                                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $employee->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No employees found matching the given criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($employees->hasPages())
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-center">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
@endsection