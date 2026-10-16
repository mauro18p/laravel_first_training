<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | WORKING FEATURES (Passing Tests)
    |--------------------------------------------------------------------------
    | These tests verify the baseline features that are already operational.
    */

    public function test_root_url_redirects_to_employees_index(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/employees');
    }

    public function test_employee_index_page_loads_successfully_and_lists_employees(): void
    {
        $department = Department::factory()->create(['name' => 'Engineering']);
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'first_name' => 'Alice',
            'last_name' => 'Walker',
            'email' => 'alice.walker@example.com',
            'job_title' => 'Software Engineer',
            'status' => 'active',
        ]);

        $response = $this->get(route('employees.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Alice Walker');
        $response->assertSeeText('Engineering');
        $response->assertSeeText('Software Engineer');
        $response->assertSeeText('alice.walker@example.com');
    }

    public function test_employee_details_page_displays_employee_information(): void
    {
        $department = Department::factory()->create(['name' => 'Marketing']);
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'job_title' => 'Marketing Lead',
            'status' => 'active',
            'phone' => '123-456-7890',
        ]);

        $response = $this->get(route('employees.show', $employee));

        $response->assertStatus(200);
        $response->assertSeeText('John Doe');
        $response->assertSeeText('Marketing');
        $response->assertSeeText('Marketing Lead');
        $response->assertSeeText('john.doe@example.com');
        $response->assertSeeText('123-456-7890');
    }

    public function test_create_employee_page_loads_successfully(): void
    {
        $response = $this->get(route('employees.create'));

        $response->assertStatus(200);
        $response->assertSeeText('Add New Employee');
        $response->assertSeeText('Employee Registration Form');
    }

    /*
    |--------------------------------------------------------------------------
    | INTENTIONALLY BROKEN & INCOMPLETE FEATURES (Failing Tests)
    |--------------------------------------------------------------------------
    | The intern must investigate the codebase, fix the issues, and get all
    | of these tests to pass.
    */

    /**
     * BUG #1: Broken Edit Button link on Index view.
     * The Edit button on the index table links to an invalid URL (/employee/{id}/edit)
     * instead of using route('employees.edit', $employee) or /employees/{id}/edit.
     */
    public function test_index_page_edit_button_points_to_valid_edit_route(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->get(route('employees.index'));

        $response->assertStatus(200);
        // The page should contain the valid edit route
        $response->assertSee(route('employees.edit', $employee), false);
    }

    /**
     * BUG #2: Controller returns the wrong view for edit action.
     * EmployeeController::edit currently returns 'employees.create' instead of 'employees.edit'.
     */
    public function test_edit_action_returns_employees_edit_view(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->get(route('employees.edit', $employee));

        $response->assertStatus(200);
        $response->assertViewIs('employees.edit');
        $response->assertSeeText('Edit Employee Profile');
    }

    /**
     * BUG #3: Update Form uses incorrect HTTP method.
     * The form in resources/views/employees/edit.blade.php is missing @method('PUT').
     */
    public function test_edit_view_contains_put_or_patch_method_spoofing(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'first_name' => 'Original',
        ]);

        // When requesting edit view, the form must include method spoofing for PUT/PATCH
        $response = $this->get(route('employees.edit', $employee));

        // When the view is fixed to employees.edit, it must contain PUT or PATCH method spoofing
        $response->assertSee('value="PUT"', false);
    }

    /**
     * BUG #4: Update action does not persist changes to the database.
     * In EmployeeController::update(), $employee->fill() is called without ->save().
     */
    public function test_employee_can_be_updated_in_database(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'first_name' => 'OriginalName',
            'job_title' => 'Junior Developer',
        ]);

        $updatePayload = [
            'department_id' => $department->id,
            'first_name' => 'UpdatedName',
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'job_title' => 'Mid-level Developer',
            'status' => 'active',
            'salary' => 80000,
            'hire_date' => '2023-01-01',
        ];

        $response = $this->put(route('employees.update', $employee), $updatePayload);

        $response->assertRedirect(route('employees.show', $employee));
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'UpdatedName',
            'job_title' => 'Mid-level Developer',
        ]);
    }

    /**
     * BUG #5: Delete button on Index view is an anchor tag making a GET request.
     * It should be a proper form submitting a DELETE request with @csrf and @method('DELETE').
     */
    public function test_index_page_delete_action_uses_delete_method_form(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);

        $response = $this->get(route('employees.index'));

        // Should have a form with DELETE method spoofing for deletion
        $response->assertSee('value="DELETE"', false);
        $response->assertSee('type="submit"', false);
    }

    /**
     * BUG #6: Storing an employee redirects to a non-existent route.
     * EmployeeController::store redirects to 'employee.index' instead of 'employees.index'.
     */
    public function test_store_employee_redirects_to_valid_employees_index_route(): void
    {
        $department = Department::factory()->create();

        $payload = [
            'department_id' => $department->id,
            'first_name' => 'Charlie',
            'last_name' => 'Chaplin',
            'email' => 'charlie@example.com',
            'job_title' => 'Actor',
            'status' => 'active',
        ];

        $response = $this->post(route('employees.store'), $payload);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('employees', [
            'email' => 'charlie@example.com',
        ]);
    }

    /**
     * INCOMPLETE FEATURE #1: Add Employee form validation is missing.
     * When required fields are missing, it should fail validation with session errors.
     */
    public function test_store_employee_requires_validation_rules(): void
    {
        $response = $this->post(route('employees.store'), []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'department_id',
            'job_title',
        ]);
    }

    /**
     * INCOMPLETE FEATURE #2: Department filtering is not implemented in the controller.
     * When department_id is passed, only employees belonging to that department should appear.
     */
    public function test_index_page_filters_by_department(): void
    {
        $itDept = Department::factory()->create(['name' => 'Information Tech']);
        $hrDept = Department::factory()->create(['name' => 'HR Operations']);

        $itEmployee = Employee::factory()->create([
            'department_id' => $itDept->id,
            'first_name' => 'Ian',
            'last_name' => 'Tech',
        ]);
        $hrEmployee = Employee::factory()->create([
            'department_id' => $hrDept->id,
            'first_name' => 'Hannah',
            'last_name' => 'Resource',
        ]);

        $response = $this->get(route('employees.index', ['department_id' => $itDept->id]));

        $response->assertStatus(200);
        $response->assertSeeText('Ian Tech');
        $response->assertDontSeeText('Hannah Resource');
    }

    /**
     * INCOMPLETE FEATURE #3: Quick status update logic is not implemented.
     * Patching the status should update the employee status in the database.
     */
    public function test_employee_status_can_be_updated_via_status_endpoint(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create([
            'department_id' => $department->id,
            'status' => 'active',
        ]);

        $response = $this->patch(route('employees.status.update', $employee), [
            'status' => 'on_leave',
        ]);

        $response->assertSessionMissing('warning');
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'status' => 'on_leave',
        ]);
    }
}
