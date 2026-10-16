<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Engineering',
                'Human Resources',
                'Marketing',
                'Finance',
                'Sales',
                'Customer Support',
                'Product Management',
                'Legal',
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
