<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $engineering = Department::where('name', 'Engineering')->first();
        $hr = Department::where('name', 'Human Resources')->first();
        $marketing = Department::where('name', 'Marketing')->first();
        $finance = Department::where('name', 'Finance')->first();
        $sales = Department::where('name', 'Sales')->first();

        $seedEmployees = [
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@example.com',
                'phone' => '555-0101',
                'department_id' => $engineering->id,
                'job_title' => 'Senior Backend Developer',
                'status' => 'active',
                'salary' => 95000.00,
                'hire_date' => '2022-03-15',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.com',
                'phone' => '555-0102',
                'department_id' => $engineering->id,
                'job_title' => 'Frontend Engineer',
                'status' => 'active',
                'salary' => 82000.00,
                'hire_date' => '2023-01-10',
            ],
            [
                'first_name' => 'Carol',
                'last_name' => 'Williams',
                'email' => 'carol.williams@example.com',
                'phone' => '555-0103',
                'department_id' => $hr->id,
                'job_title' => 'HR Specialist',
                'status' => 'on_leave',
                'salary' => 65000.00,
                'hire_date' => '2021-08-01',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@example.com',
                'phone' => '555-0104',
                'department_id' => $finance->id,
                'job_title' => 'Financial Analyst',
                'status' => 'active',
                'salary' => 78000.00,
                'hire_date' => '2020-11-20',
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Davis',
                'email' => 'emma.davis@example.com',
                'phone' => '555-0105',
                'department_id' => $marketing->id,
                'job_title' => 'Content Strategist',
                'status' => 'inactive',
                'salary' => 62000.00,
                'hire_date' => '2023-06-01',
            ],
            [
                'first_name' => 'Franklin',
                'last_name' => 'Miller',
                'email' => 'franklin.miller@example.com',
                'phone' => '555-0106',
                'department_id' => $sales->id,
                'job_title' => 'Account Executive',
                'status' => 'active',
                'salary' => 71000.00,
                'hire_date' => '2022-09-12',
            ],
        ];

        foreach ($seedEmployees as $emp) {
            Employee::firstOrCreate(
                ['email' => $emp['email']],
                $emp
            );
        }

        // Create 20 more realistic random employees using factory
        $departments = Department::all();
        foreach (range(1, 20) as $i) {
            Employee::factory()->create([
                'department_id' => $departments->random()->id,
            ]);
        }
    }
}
