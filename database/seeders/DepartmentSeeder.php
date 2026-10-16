<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering', 'description' => 'Software development and IT operations'],
            ['name' => 'Human Resources', 'description' => 'People operations, recruiting, and culture'],
            ['name' => 'Marketing', 'description' => 'Brand strategy, content, and digital marketing'],
            ['name' => 'Finance', 'description' => 'Accounting, financial planning, and payroll'],
            ['name' => 'Sales', 'description' => 'Direct sales, partnerships, and client relations'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                ['description' => $dept['description']]
            );
        }
    }
}
