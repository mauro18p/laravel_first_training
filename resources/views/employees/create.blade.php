@extends('layouts.app')

@section('title', 'Add New Employee - EMS Portal')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Employee</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 mb-0">Add New Employee</h2>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Directory
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0">Employee Registration Form</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="first_name"
                           name="first_name"
                           value="{{ old('first_name') }}">
                </div>

                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="last_name"
                           name="last_name"
                           value="{{ old('last_name') }}">
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="{{ old('email') }}">
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text"
                           class="form-control"
                           id="phone"
                           name="phone"
                           value="{{ old('phone') }}">
                </div>

                <div class="col-md-6">
                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Select a department...</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="job_title"
                           name="job_title"
                           value="{{ old('job_title') }}">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="salary" class="form-label">Annual Salary (USD)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               step="0.01"
                               class="form-control"
                               id="salary"
                               name="salary"
                               placeholder="e.g. 75000"
                               value="{{ old('salary') }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="hire_date" class="form-label">Hire Date</label>
                    <input type="date"
                           class="form-control"
                           id="hire_date"
                           name="hire_date"
                           value="{{ old('hire_date') }}">
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
