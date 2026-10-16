# 🐛 Internal Bug Tracker & Ticket Backlog

Welcome to your first week on the engineering team! Here is our internal ticket backlog for the **Employee Management System (EMS)**.

As an intern, your task is to triage each issue, inspect the relevant codebase files, determine the root cause, write the fix, and verify it using both manual steps and our automated PHPUnit test suite.

> **Rules of Engagement:**
> - Do not modify working features unless necessary for a fix.
> - Always run the corresponding automated test to verify your fix.
> - Keep your changes focused and idiomatic to Laravel.

---

## Ticket Backlog Summary

| Ticket ID | Category | Summary | Priority |
|---|---|---|---|
| **EMS-101** | Broken Feature | Edit button on dashboard table returns 404 Not Found | High |
| **EMS-102** | Controller Bug | Edit action displays the "Add New Employee" form instead of the edit profile view | High |
| **EMS-103** | Broken Feature / HTTP Method | Submitting the employee edit form results in `405 Method Not Allowed` | Critical |
| **EMS-104** | Data Persistence Bug | Updating employee details flashes success, but changes are not saved to the database | Critical |
| **EMS-105** | Broken Feature | Clicking the "Delete" button opens the view details page instead of deleting | High |
| **EMS-106** | Broken Feature | Clicking the "Search" button on the dashboard does nothing, and search queries do not filter results | Medium |
| **EMS-107** | Routing Bug | Submitting the "Add Employee" form throws `RouteNotFoundException: Route [employee.index] not defined` | High |
| **EMS-108** | Incomplete Feature | Add Employee form has no input validation; invalid or empty submissions cause SQL errors | High |
| **EMS-109** | Incomplete Feature | Department filter dropdown does not filter the employee directory | Medium |
| **EMS-110** | Incomplete Feature | "Update Status" on the employee details page triggers a warning notice without updating the record | Medium |

---

## Detailed Ticket Descriptions

### 🎫 EMS-101: Edit button on dashboard returns 404 Not Found
- **Category:** Broken Feature (UI / Routing)
- **Symptoms:** When an administrator browses the employee list on the dashboard and clicks the blue "Edit" button for any employee, the browser navigates to a 404 Not Found error page.
- **Steps to Reproduce:**
  1. Open the dashboard at `http://localhost:8000/employees`.
  2. Locate any employee row (e.g. Employee #1).
  3. Click the **Edit** button in the Actions column.
  4. Notice the resulting URL in your browser address bar and the 404 error response.
- **Expected Behavior:** Clicking "Edit" should navigate to the employee edit page (`/employees/{id}/edit`).
- **Files to Inspect:** `resources/views/employees/index.blade.php`, `routes/web.php`
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_index_page_edit_button_points_to_valid_edit_route
  ```
- **Mentor Guidance:** Check the URL structure generated in the Blade template. Run `php artisan route:list --path=employees` to see how our registered employee routes are structured.

---

### 🎫 EMS-102: Edit action renders the "Add New Employee" form instead of the edit view
- **Category:** Controller Bug / View Rendering
- **Symptoms:** Even when navigating directly to `/employees/1/edit`, the page header says "Add New Employee", the input fields are blank, and the submit button says "Save Employee".
- **Steps to Reproduce:**
  1. Navigate to `http://localhost:8000/employees/1/edit` in your browser.
  2. Notice the form title, blank inputs, and action.
- **Expected Behavior:** Navigating to an employee's edit URL should render the edit form (`resources/views/employees/edit.blade.php`) populated with that employee's current details.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (specifically the `edit()` method)
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_edit_action_returns_employees_edit_view
  ```
- **Mentor Guidance:** Check the return statement inside the controller's `edit()` method. Which Blade view is it rendering?

---

### 🎫 EMS-103: Submitting the employee edit form results in `405 Method Not Allowed`
- **Category:** Broken Feature / HTTP Method Spoofing
- **Symptoms:** When submitting the edit form, Laravel halts with `Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException: The POST method is not supported for route employees/1. Supported methods: GET, HEAD, PUT, PATCH, DELETE.`
- **Steps to Reproduce:**
  1. Open the edit form at `http://localhost:8000/employees/1/edit`.
  2. Make a change to a field (e.g., job title) and click **Update Employee**.
  3. The error screen triggers with HTTP status 405.
- **Expected Behavior:** Submitting the form should successfully hit the update route (`employees.update`).
- **Files to Inspect:** `resources/views/employees/edit.blade.php`
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_edit_view_contains_put_or_patch_method_spoofing
  ```
- **Mentor Guidance:** HTML forms natively only support `GET` and `POST`. How does Laravel handle `PUT` and `PATCH` requests coming from standard HTML forms?

---

### 🎫 EMS-104: Updating employee details does not persist changes to the database
- **Category:** Data Persistence Bug
- **Symptoms:** When submitting the edit form, the user is redirected to the employee profile with the flash message *"Employee updated successfully."*, but none of the modified values are saved. The profile still displays the old data.
- **Steps to Reproduce:**
  1. Go to `http://localhost:8000/employees/1/edit`.
  2. Change the First Name from "Alice" to "Alison" and submit.
  3. Notice the success message, but examine the First Name: it remains "Alice".
- **Expected Behavior:** Modified attributes should be saved to the database and reflected immediately on the profile.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (specifically the `update()` method)
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_employee_can_be_updated_in_database
  ```
- **Mentor Guidance:** Review the Eloquent Model lifecycle methods in `EmployeeController::update()`. Is `fill()` sufficient on its own, or is an explicit persistence call missing?

---

### 🎫 EMS-105: Delete button opens employee profile instead of deleting
- **Category:** Broken Feature / HTTP Method
- **Symptoms:** In the dashboard table, clicking the red "Delete" button for an employee triggers a JavaScript confirmation dialog, but upon clicking "OK", the browser simply opens that employee's View details page. No record is removed.
- **Steps to Reproduce:**
  1. Go to `http://localhost:8000/employees`.
  2. Click the **Delete** button on any row.
  3. Confirm the alert popup.
  4. Notice you are redirected to the details page (`/employees/{id}`). Check the directory; the employee is still present.
- **Expected Behavior:** Confirming deletion should delete the employee from the database and redirect back to the employee directory with a success message.
- **Files to Inspect:** `resources/views/employees/index.blade.php`, `routes/web.php`
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_index_page_delete_action_uses_delete_method_form
  ```
- **Mentor Guidance:** Inspect the HTML element representing the Delete button on the index view. Is an `<a>` tag capable of issuing a `DELETE` HTTP request to a Laravel route? (Hint: compare how deletion is handled on `show.blade.php` in the Danger Zone).

---

### 🎫 EMS-106: Search button does nothing and query parameters are mismatched
- **Category:** Broken Feature / Form Interaction
- **Symptoms:** 
  1. Clicking the "Search" button on the dashboard table does not submit the form at all.
  2. If the user presses "Enter" inside the search box, the page refreshes, but the table results are not filtered by the search term.
- **Steps to Reproduce:**
  1. Go to `http://localhost:8000/employees`.
  2. Type "Alice" into the search field.
  3. Click the blue **Search** button. Notice nothing happens.
  4. Click into the input and press the **Enter** key. Notice the URL updates with `?search_query=Alice`, but all employees are still shown in the list.
- **Expected Behavior:** Clicking the Search button or pressing Enter should filter the employee list by matching first name, last name, email, or job title.
- **Files to Inspect:** `resources/views/employees/index.blade.php`, `app/Http/Controllers/EmployeeController.php`
- **Mentor Guidance:** Check the `type` attribute on the search button element. Then compare the input's `name` attribute with the parameter key retrieved in `EmployeeController::index()`.

---

### 🎫 EMS-107: Adding an employee causes `RouteNotFoundException: Route [employee.index] not defined`
- **Category:** Routing Bug / Named Route Typo
- **Symptoms:** Filling out the "Add Employee" form and clicking "Save Employee" results in a 500 error page with `Symfony\Component\Routing\Exception\RouteNotFoundException: Route [employee.index] not defined.`
- **Steps to Reproduce:**
  1. Click **Add Employee** from the top navbar or dashboard.
  2. Fill in all fields with valid test data.
  3. Click **Save Employee**.
  4. The application crashes with `RouteNotFoundException`.
- **Expected Behavior:** After saving, the user should be redirected back to the employee list (`employees.index`) with a success message.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (specifically `store()`), `routes/web.php`
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_store_employee_redirects_to_valid_employees_index_route
  ```
- **Mentor Guidance:** Look closely at the route name specified in the `redirect()->route(...)` call in `EmployeeController::store()`. Does it match our registered resource routes?

---

### 🎫 EMS-108: Add Employee form allows empty submissions and lacks validation
- **Category:** Incomplete Feature / Request Validation
- **Symptoms:** Submitting the "Add Employee" form with blank fields either bypasses validation or crashes with a database integrity constraint violation (`NOT NULL constraint failed`). The form also doesn't display error messages next to invalid fields.
- **Steps to Reproduce:**
  1. Navigate to `/employees/create`.
  2. Click **Save Employee** without typing anything.
  3. Notice that no validation errors are displayed.
- **Expected Behavior:** The form submission should validate required fields (`first_name`, `last_name`, `email`, `department_id`, `job_title`), ensure email format and uniqueness, and display field-level feedback when validation fails.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (in `store()`), `resources/views/employees/create.blade.php`
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_store_employee_requires_validation_rules
  ```
- **Mentor Guidance:** Add `$request->validate([...])` (or create a dedicated Form Request via `php artisan make:request StoreEmployeeRequest`). In `create.blade.php`, incorporate `@error` directives and Bootstrap's `.is-invalid` / `.invalid-feedback` classes.

---

### 🎫 EMS-109: Department filter dropdown is ignored by the controller
- **Category:** Incomplete Feature / Database Query
- **Symptoms:** Selecting a department from the dropdown and clicking Search updates the query string (e.g. `?department_id=2`), but the table continues to show employees from all departments.
- **Steps to Reproduce:**
  1. Go to `http://localhost:8000/employees`.
  2. Select "Human Resources" from the department dropdown.
  3. Submit the filter.
  4. Notice the table still lists employees belonging to Engineering, Finance, etc.
- **Expected Behavior:** Only employees assigned to the selected department should be displayed.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (inside `index()`)
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_index_page_filters_by_department
  ```
- **Mentor Guidance:** Look inside `EmployeeController::index()`. Notice the `// TODO` comment where department filtering was left unimplemented. How should the Eloquent query be scoped when `department_id` is present?

---

### 🎫 EMS-110: Status update action displays a placeholder warning
- **Category:** Incomplete Feature / State Update
- **Symptoms:** On the employee profile page (`/employees/{id}`), using the "Change Status" form and clicking "Update Status" reloads the page with a yellow warning: *"Status update feature is under development."* The status is not changed.
- **Steps to Reproduce:**
  1. Go to `/employees/1`.
  2. Change status to "On Leave" or "Inactive".
  3. Click **Update Status**.
  4. Notice the warning alert and unchanged badge.
- **Expected Behavior:** The employee's status should update in the database, and the page should refresh showing the updated status badge and a success flash message.
- **Files to Inspect:** `app/Http/Controllers/EmployeeController.php` (method `updateStatus()`)
- **Automated Test to Run:**
  ```bash
  php artisan test --filter=test_employee_status_can_be_updated_via_status_endpoint
  ```
- **Mentor Guidance:** Check the `updateStatus` method in `EmployeeController`. Validate the incoming status (`active`, `on_leave`, `inactive`), update the model attribute, save it, and return a proper redirect.

---

## Testing Commands Quick Reference

Run only the passing tests:
```bash
php artisan test --filter=test_employee_index_page_loads_successfully_and_lists_employees
php artisan test --filter=test_employee_details_page_displays_employee_information
```

Run all tests across the suite:
```bash
php artisan test
```
