@extends('layouts.app')

@section('title', $employee->name . ' - Employee Details')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $employee->name }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 mb-0">{{ $employee->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Profile
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Main Employee Info Card --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Personal & Employment Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Employee ID</label>
                        <p class="fs-5 fw-bold text-dark">#{{ $employee->id }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Department</label>
                        <p class="fs-5 text-dark">
                            <span class="badge bg-light text-dark border">
                                {{ $employee->department ? $employee->department->name : 'Unassigned' }}
                            </span>
                        </p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">First Name</label>
                        <p class="fs-5 text-dark">{{ $employee->first_name }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Last Name</label>
                        <p class="fs-5 text-dark">{{ $employee->last_name }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Email Address</label>
                        <p class="fs-5 text-dark">
                            <a href="mailto:{{ $employee->email }}" class="text-decoration-none">{{ $employee->email }}</a>
                        </p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Phone Number</label>
                        <p class="fs-5 text-dark">{{ $employee->phone ?? 'Not provided' }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Job Title</label>
                        <p class="fs-5 text-dark">{{ $employee->job_title }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Annual Salary</label>
                        <p class="fs-5 text-dark">{{ $employee->salary ? '$' . number_format($employee->salary, 2) : 'Not specified' }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Hire Date</label>
                        <p class="fs-5 text-dark">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'Not specified' }}</p>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold">Record Created</label>
                        <p class="fs-5 text-dark">{{ $employee->created_at ? $employee->created_at->format('M d, Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar: Status and Quick Actions --}}
    <div class="col-lg-4">
        {{-- Status card --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Employment Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-semibold d-block mb-1">Current Status</label>
                    @if ($employee->status === 'active')
                        <span class="badge bg-success fs-6 py-2 px-3">Active</span>
                    @elseif ($employee->status === 'on_leave')
                        <span class="badge bg-warning text-dark fs-6 py-2 px-3">On Leave</span>
                    @else
                        <span class="badge bg-secondary fs-6 py-2 px-3">Inactive</span>
                    @endif
                </div>

                <hr>

                {{-- Status update form (Incomplete feature) --}}
                <h6 class="fw-bold mb-2">Change Status</h6>
                <p class="text-muted small mb-3">Update this staff member's working availability.</p>

                <form action="{{ route('employees.status.update', $employee->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <select name="status" class="form-select" required>
                            <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_leave" {{ $employee->status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="inactive" {{ $employee->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="card border-danger">
            <div class="card-header bg-danger-subtle text-danger py-3">
                <h6 class="card-title mb-0 fw-bold">Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Deleting this record will permanently remove all associated information.</p>
                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this employee?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Delete Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
