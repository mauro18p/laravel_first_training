<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('employees.index');
});

Route::resource('employees', EmployeeController::class);
Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status.update');
