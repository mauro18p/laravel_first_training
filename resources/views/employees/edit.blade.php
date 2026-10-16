@extends('layouts.app')

@section('title', 'Edit Employee - ' . $employee->name)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employees.show', $employee->id) }}">{{ $employee->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="h3 mb-0">Edit Employee Profile</h2>
        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Profile
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0">Editing #{{ $employee->id }} — {{ $employee->name }}</h5>
    </div>
    <div class="card-body">
        {{-- BUG: Notice the HTTP method and lack of method spoofing below --}}
        <form action="{{ route('employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="first_name"
                           name="first_name"
                           value="{{ old('first_name', $employee->first_name) }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           id="last_name"
                           name="last_name"
                           value="{{ old('last_name', $employee->last_name) }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="{{ old('email', $employee->email) }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text"
                           class="form-control"
                           id="phone"
                           name="phone"
                           value="{{ old('phone', $employee->phone) }}">
                </div>

                <div class="col-md-6">
                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="department_id" name="department_id" required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
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
                           value="{{ old('job_title', $employee->job_title) }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ old('status', $employee->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                               value="{{ old('salary', $employee->salary) }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="hire_date" class="form-label">Hire Date</label>
                    <input type="date"
                           class="form-control"
                           id="hire_date"
                           name="hire_date"
                           value="{{ old('hire_date', optional($employee->hire_date)->format('Y-m-d')) }}">
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
