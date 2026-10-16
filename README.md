# Laravel Bug-Fixing & CRUD Reference Guide

> A practical reference built from real hands-on debugging exercises.
> Use this guide whenever you encounter broken routes, forms, controllers, or database interactions in a Laravel application.

---

## Table of Contents

- [Part 1 — Exercises Completed](#part-1--exercises-completed)
  - [EMS-101: Broken URL in Blade Template (404 Not Found)](#ems-101-broken-url-in-blade-template-404-not-found)
  - [EMS-102: Controller Returning the Wrong View](#ems-102-controller-returning-the-wrong-view)
  - [EMS-103: Missing HTTP Method Spoofing (405 Method Not Allowed)](#ems-103-missing-http-method-spoofing-405-method-not-allowed)
  - [EMS-104: Eloquent Model Not Persisting Changes](#ems-104-eloquent-model-not-persisting-changes)
  - [EMS-105: Delete Button Using GET Instead of DELETE](#ems-105-delete-button-using-get-instead-of-delete)
  - [EMS-106: Search Button Not Submitting & Mismatched Parameter Names](#ems-106-search-button-not-submitting--mismatched-parameter-names)
  - [EMS-107: Incorrect Named Route (RouteNotFoundException)](#ems-107-incorrect-named-route-routenotfoundexception)
  - [EMS-108: Missing Form Validation](#ems-108-missing-form-validation)
  - [EMS-109: Incomplete Query Filtering in Controller](#ems-109-incomplete-query-filtering-in-controller)
  - [EMS-110: Incomplete Feature — Status Update Endpoint](#ems-110-incomplete-feature--status-update-endpoint)
- [Part 2 — Common Laravel Problems & Solutions](#part-2--common-laravel-problems--solutions)
  - [Routing Problems](#1-routing-problems)
  - [Controller & View Problems](#2-controller--view-problems)
  - [Form & HTTP Method Problems](#3-form--http-method-problems)
  - [Database & Eloquent Problems](#4-database--eloquent-problems)
  - [Validation Problems](#5-validation-problems)
  - [Environment & Configuration Problems](#6-environment--configuration-problems)
  - [Frontend & Blade Problems](#7-frontend--blade-problems)
  - [Debugging Workflow Checklist](#8-debugging-workflow-checklist)

---

## Part 1 — Exercises Completed

### EMS-101: Broken URL in Blade Template (404 Not Found)

**Symptom:** Clicking the Edit button in the employee table returned a 404 Not Found error.

**Root Cause:** The Blade template used a hardcoded URL with a typo — singular `employee` instead of plural `employees`:

```blade
{{-- ❌ Broken --}}
<a href="{{ url('/employee/' . $employee->id . '/edit') }}">Edit</a>
```

**Fix:** Replace the hardcoded `url()` call with a named route:

```blade
{{-- ✅ Fixed --}}
<a href="{{ route('employees.edit', $employee->id) }}">Edit</a>
```

**Why It Works:** Named routes decouple your links from the actual URL pattern. If you ever change the URL in `routes/web.php`, every `route('employees.edit', ...)` call updates automatically. Hardcoded URL strings silently break.

**How to Diagnose:**
1. Look at the URL in the browser address bar when the 404 occurs.
2. Run `php artisan route:list --path=employees` to see the registered URL patterns.
3. Compare them — mismatches mean the link is wrong.

---

### EMS-102: Controller Returning the Wrong View

**Symptom:** Navigating to `/employees/1/edit` showed the "Add New Employee" form (blank fields, wrong title) instead of the edit form with pre-filled data.

**Root Cause:** The `edit()` method in `EmployeeController` returned the wrong Blade view:

```php
// ❌ Broken — returns the create view
return view('employees.create', compact('employee', 'departments'));
```

**Fix:** Return the correct view name:

```php
// ✅ Fixed
return view('employees.edit', compact('employee', 'departments'));
```

**Why It Works:** Laravel's `view()` function loads the Blade template matching the dot-notation path. `employees.create` resolves to `resources/views/employees/create.blade.php`, while `employees.edit` resolves to `resources/views/employees/edit.blade.php`. These are completely different files with different layouts, titles, and form behaviors.

**How to Diagnose:**
1. Inspect the page in the browser — read the page title and form action to identify which template was rendered.
2. Open the controller method and check the `return view(...)` statement.
3. Verify the view file exists at the expected path.

---

### EMS-103: Missing HTTP Method Spoofing (405 Method Not Allowed)

**Symptom:** Submitting the edit form caused a `405 Method Not Allowed` error: _"The POST method is not supported for route employees/{employee}. Supported methods: GET, HEAD, PUT, PATCH, DELETE."_

**Root Cause:** The edit form's `<form>` tag had `method="POST"` but was missing `@method('PUT')`:

```blade
{{-- ❌ Broken — sends raw POST, but route expects PUT --}}
<form action="{{ route('employees.update', $employee->id) }}" method="POST">
    @csrf
    ...
</form>
```

**Fix:** Add the `@method('PUT')` directive right after `@csrf`:

```blade
{{-- ✅ Fixed --}}
<form action="{{ route('employees.update', $employee->id) }}" method="POST">
    @csrf
    @method('PUT')
    ...
</form>
```

**Why It Works:** HTML `<form>` elements only support `GET` and `POST` natively. Browsers cannot send `PUT`, `PATCH`, or `DELETE` through forms. Laravel solves this with **method spoofing**: `@method('PUT')` renders a hidden input `<input type="hidden" name="_method" value="PUT">`. When Laravel receives the request, its middleware reads `_method` and treats the request as a `PUT` instead of `POST`.

**How to Diagnose:**
1. A `405 Method Not Allowed` error always means the HTTP verb is wrong.
2. Check the form's `method` attribute and look for `@method()`.
3. Run `php artisan route:list` to see which verbs the target route accepts.

---

### EMS-104: Eloquent Model Not Persisting Changes

**Symptom:** The update form submitted successfully and showed a "Employee updated successfully" flash message, but when revisiting the profile page, none of the changes were saved.

**Root Cause:** The `update()` method called `$employee->fill()` but never called `$employee->save()`:

```php
// ❌ Broken — fill() only sets attributes in memory, nothing is written to the database
$employee->fill($request->all());
// Missing: $employee->save();
```

**Fix:** Call `save()` after `fill()`:

```php
// ✅ Fixed
$employee->fill($request->all());
$employee->save();
```

Or use the shorthand `update()`:

```php
// ✅ Alternative (even cleaner)
$employee->update($request->all());
```

**Why It Works:**
- `fill()` populates the model's attributes in PHP memory but does **not** execute any SQL.
- `save()` executes `UPDATE employees SET ... WHERE id = ?` to actually write changes to the database.
- `update()` combines both steps — it fills and saves in a single call.

**How to Diagnose:**
1. If a success message appears but data doesn't change, the redirect is working but the database write is missing.
2. Search the controller method for `save()`, `update()`, or `push()` calls.
3. Check your database directly (e.g. with `php artisan tinker` or a database client) to confirm whether the data changed.

---

### EMS-105: Delete Button Using GET Instead of DELETE

**Symptom:** Clicking the red "Delete" button on the table confirmed via JavaScript alert, but instead of deleting the employee, the browser navigated to the employee's View Details page.

**Root Cause:** The Delete action was an `<a>` tag, which only sends `GET` requests:

```blade
{{-- ❌ Broken — anchor tags always send GET requests --}}
<a href="{{ route('employees.destroy', $employee->id) }}"
   onclick="return confirm('Delete?');">
    Delete
</a>
```

Since the `GET /employees/{id}` route maps to `show` (View Details), clicking the link opened the profile instead of deleting.

**Fix:** Replace the anchor tag with a proper `<form>` that sends a `DELETE` request:

```blade
{{-- ✅ Fixed --}}
<form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
      class="d-inline"
      onsubmit="return confirm('Are you sure?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-trash"></i> Delete
    </button>
</form>
```

**Why It Works:** Destructive actions (`DELETE`, `PUT`, `PATCH`) must never be performed via `GET` requests. A web crawler, browser pre-fetcher, or even a user's browser extension could follow `GET` links and accidentally trigger data changes. Using a `<form>` with `@method('DELETE')` ensures the request uses the correct HTTP verb and is protected by CSRF.

> [!IMPORTANT]
> **Security Rule:** Never perform destructive actions (delete, update) via `GET` requests. Always use forms with `@csrf` and the appropriate `@method()`.

---

### EMS-106: Search Button Not Submitting & Mismatched Parameter Names

**Symptom:** Two separate issues:
1. Clicking the "Search" button did nothing at all.
2. Pressing Enter in the search box refreshed the page, but results were not filtered.

**Root Cause (Issue 1):** The search button had `type="button"` instead of `type="submit"`:

```blade
{{-- ❌ type="button" does nothing inside a form --}}
<button type="button" class="btn btn-primary" id="btnSearch">Search</button>
```

**Root Cause (Issue 2):** The form input was named `search_query` but the controller looked for `search`:

```blade
{{-- View sends "search_query" --}}
<input type="text" name="search_query" ...>
```

```php
// Controller checks for "search" — never sees search_query!
if ($request->filled('search')) {
    $search = $request->input('search');
```

**Fix:** Change button type and make the parameter name consistent:

```blade
{{-- ✅ Fixed: type="submit" and name="search" --}}
<input type="text" name="search" value="{{ request('search') }}" ...>
<button type="submit" class="btn btn-primary">Search</button>
```

**Why It Works:**
- `type="button"` creates a generic button that does nothing unless JavaScript handles it. `type="submit"` tells the browser to submit the parent `<form>`.
- When the Blade input `name` and the PHP `$request->input()` key match, Laravel correctly reads the value from the query string.

**How to Diagnose:**
1. Check the `type` attribute of your `<button>` elements.
2. Compare the `name` attribute in the HTML input with the key used in `$request->input()` or `$request->filled()`.
3. Use your browser's DevTools Network tab to see the actual query string being sent.

---

### EMS-107: Incorrect Named Route (RouteNotFoundException)

**Symptom:** After submitting the "Add Employee" form, the app crashed with: `RouteNotFoundException: Route [employee.index] not defined.`

**Root Cause:** A typo in the route name used for redirect — singular `employee.index` instead of plural `employees.index`:

```php
// ❌ Broken — "employee" (singular) is not a registered route name
return redirect()->route('employee.index');
```

**Fix:** Use the correct route name:

```php
// ✅ Fixed
return redirect()->route('employees.index');
```

**Why It Works:** Laravel resource routes follow the convention `{plural_resource}.{action}`. When you register `Route::resource('employees', ...)`, Laravel creates `employees.index`, `employees.store`, `employees.show`, etc. Using a different name like `employee.index` doesn't match any registered route.

**How to Diagnose:**
1. `RouteNotFoundException` always means the string passed to `route()` doesn't match any named route.
2. Run `php artisan route:list --name=employee` to see what's actually registered.
3. Check for typos: singular vs. plural, missing dots, wrong suffixes.

---

### EMS-108: Missing Form Validation

**Symptom:** Submitting the "Add Employee" form with blank fields caused a raw SQL constraint violation instead of user-friendly validation errors.

**Root Cause:** The `store()` method had no validation — it passed raw input directly to `Employee::create()`:

```php
// ❌ Broken — no validation, crashes on NOT NULL constraints
$employee = Employee::create($request->all());
```

**Fix:** Add validation rules before creating the employee:

```php
// ✅ Fixed
$validated = $request->validate([
    'first_name'    => 'required|string|max:255',
    'last_name'     => 'required|string|max:255',
    'email'         => 'required|email|unique:employees,email',
    'department_id' => 'required|exists:departments,id',
    'job_title'     => 'required|string|max:255',
    'phone'         => 'nullable|string|max:255',
    'status'        => 'required|in:active,on_leave,inactive',
    'salary'        => 'nullable|numeric|min:0',
    'hire_date'     => 'nullable|date',
]);

$employee = Employee::create($validated);
```

**Why It Works:** `$request->validate()` checks every field against its rules. If any rule fails, Laravel automatically redirects back to the form with the error messages in the session (accessible via `@error` in Blade). If all rules pass, it returns only the validated data, which is safe to insert into the database.

> [!TIP]
> Use `$validated` (the return value of `validate()`) instead of `$request->all()` when creating models. This prevents mass-assignment of unexpected fields that a user might inject into the request.

---

### EMS-109: Incomplete Query Filtering in Controller

**Symptom:** Selecting a department from the dropdown and submitting showed the `department_id` in the URL, but all employees from every department were still displayed.

**Root Cause:** The controller's `index()` method had a `// TODO` placeholder where filtering should have been:

```php
// TODO (Intern Task): Implement department filtering
```

**Fix:** Add a conditional `where` clause for `department_id`:

```php
// ✅ Fixed
if ($request->filled('department_id')) {
    $query->where('department_id', $request->input('department_id'));
}
```

**Why It Works:** `$request->filled('department_id')` returns `true` only when the parameter is present **and not empty**. When the user selects "All Departments" (`value=""`), `filled()` returns `false` and the filter is skipped, showing all employees. When a specific department is selected (e.g. `value="2"`), the query adds a `WHERE department_id = 2` clause.

---

### EMS-110: Incomplete Feature — Status Update Endpoint

**Symptom:** Clicking "Update Status" on the employee details page displayed a yellow warning "Status update feature is under development" without changing anything.

**Root Cause:** The `updateStatus()` method was a placeholder that only returned a warning flash:

```php
// ❌ Placeholder — no actual logic
return redirect()->back()
    ->with('warning', 'Status update feature is under development.');
```

**Fix:** Validate the status, update the employee, and return a success message:

```php
// ✅ Fixed
public function updateStatus(Request $request, Employee $employee): RedirectResponse
{
    $validated = $request->validate([
        'status' => 'required|in:active,on_leave,inactive',
    ]);

    $employee->update($validated);

    return redirect()->back()
        ->with('success', 'Employee status updated successfully.');
}
```

**Why It Works:** The `in:active,on_leave,inactive` validation rule ensures only valid status values are accepted. `$employee->update()` fills and saves in one call. Returning `->with('success', ...)` instead of `->with('warning', ...)` triggers the green success alert in the layout.

---

## Part 2 — Common Laravel Problems & Solutions

### 1. Routing Problems

#### 1.1 — 404 Not Found on a page you created

| Check | How |
|---|---|
| Is the route registered? | `php artisan route:list --path=your-path` |
| Is the URL spelled correctly? | Compare browser URL with route:list output |
| Is the controller method defined? | Open the controller file and look for the method |
| Route caching? | `php artisan route:clear` |

#### 1.2 — RouteNotFoundException: Route [name] not defined

This always means the string inside `route('...')` doesn't match any registered named route.

```bash
# Find all registered route names
php artisan route:list --name=employees
```

Common mistakes:
- Singular vs. plural (`employee.index` vs. `employees.index`)
- Missing resource registration in `routes/web.php`
- Typos in dot notation

#### 1.3 — Route Model Binding returns 404

If your route uses `{employee}` and you get a 404 when passing a valid ID, check:
- The parameter name in the route matches the variable name in the controller: `Route::get('/employees/{employee}', ...)` → `public function show(Employee $employee)`
- The model actually exists in the database with that ID

---

### 2. Controller & View Problems

#### 2.1 — Page shows wrong content

```php
// Always double-check the view name in your return statement:
return view('employees.edit', compact('employee', 'departments'));
//           ^^^^^^^^^^^^^^ — does this match the actual file path?
```

The view string maps to a file path:
- `'employees.edit'` → `resources/views/employees/edit.blade.php`
- `'employees.create'` → `resources/views/employees/create.blade.php`

#### 2.2 — "Undefined variable" in Blade

The variable was not passed from the controller. Check the `compact()` call or the array passed to `view()`:

```php
// ❌ Missing 'departments' — Blade will throw "Undefined variable: departments"
return view('employees.create', compact('employee'));

// ✅ Include all variables the view needs
return view('employees.create', compact('employee', 'departments'));
```

#### 2.3 — Controller method not being called

Ensure the route is pointing to the correct controller and method:

```bash
php artisan route:list --path=employees
# Check the "Action" column matches the method you expect
```

---

### 3. Form & HTTP Method Problems

#### 3.1 — Quick Reference: Method Spoofing

| Action | HTTP Verb | Form Setup |
|---|---|---|
| Create | `POST` | `method="POST"` + `@csrf` |
| Update | `PUT` / `PATCH` | `method="POST"` + `@csrf` + `@method('PUT')` |
| Delete | `DELETE` | `method="POST"` + `@csrf` + `@method('DELETE')` |

> [!IMPORTANT]
> HTML forms only support `GET` and `POST`. For `PUT`, `PATCH`, and `DELETE`, always use `method="POST"` combined with Laravel's `@method()` directive.

#### 3.2 — 419 Page Expired (CSRF Token Mismatch)

Every `POST`, `PUT`, `PATCH`, and `DELETE` form needs `@csrf`:

```blade
<form method="POST" action="{{ route('employees.store') }}">
    @csrf   {{-- Always include this! --}}
    ...
</form>
```

If you get a 419 error:
1. Check `@csrf` is present inside the `<form>`.
2. Clear session: `php artisan session:clear` or restart the browser.
3. Check your `SESSION_DRIVER` in `.env` is configured correctly.

#### 3.3 — Form data not reaching the controller

| Symptom | Likely Cause |
|---|---|
| `$request->input('field')` returns `null` | The input `name` attribute doesn't match |
| `$request->all()` is empty | The form `method` is `GET` but the route expects `POST` |
| Button click does nothing | Button `type` is `"button"` instead of `"submit"` |

---

### 4. Database & Eloquent Problems

#### 4.1 — Data changes not saving

Check that you call a **persistence method** after modifying the model:

```php
// fill() alone does NOT save
$employee->fill($data);    // ⚠️ In-memory only
$employee->save();          // ✅ Writes to database

// Shorthand alternatives that DO save:
$employee->update($data);  // ✅ fill + save combined
Employee::create($data);   // ✅ Creates and saves a new record
```

#### 4.2 — Mass Assignment: "Add [field] to fillable property"

If you get `MassAssignmentException`, the field is not listed in the model's `$fillable` array:

```php
// In the Model:
protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'department_id', // ← Every field you want to mass-assign must be listed here
    // ...
];
```

#### 4.3 — Integrity Constraint Violation (NOT NULL)

The database rejected an `INSERT` or `UPDATE` because a required column received `null`:

- **Cause 1:** Validation is missing, so empty form fields pass through as `null`.
- **Cause 2:** The `name` attribute in the Blade input doesn't match the database column name.
- **Cause 3:** The column in the migration doesn't have `->nullable()` but the field is optional.

#### 4.4 — "Database connection [xyz] not configured"

Check your `.env` file for typos in `DB_CONNECTION`:

```env
# ❌ Typo
DB_CONNECTION=myqls

# ✅ Correct values:
DB_CONNECTION=sqlite
DB_CONNECTION=mysql
DB_CONNECTION=pgsql
```

After changing `.env`, always run:
```bash
php artisan config:clear
```

---

### 5. Validation Problems

#### 5.1 — Adding validation to a controller method

```php
public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'first_name'    => 'required|string|max:255',
        'last_name'     => 'required|string|max:255',
        'email'         => 'required|email|unique:employees,email',
        'department_id' => 'required|exists:departments,id',
        'job_title'     => 'required|string|max:255',
        'status'        => 'required|in:active,on_leave,inactive',
        'salary'        => 'nullable|numeric|min:0',
        'hire_date'     => 'nullable|date',
    ]);

    Employee::create($validated);

    return redirect()->route('employees.index')
        ->with('success', 'Employee created.');
}
```

#### 5.2 — Common validation rules

| Rule | Purpose |
|---|---|
| `required` | Field must be present and not empty |
| `string` | Must be a string |
| `max:255` | Maximum 255 characters |
| `email` | Must be a valid email format |
| `unique:table,column` | No duplicate values in that table/column |
| `exists:table,column` | The value must exist in that table/column (foreign key check) |
| `in:a,b,c` | Value must be one of the listed options |
| `nullable` | Field is allowed to be empty/null |
| `numeric` | Must be a number |
| `date` | Must be a valid date |
| `min:0` | Minimum numeric value |
| `confirmed` | Requires a matching `_confirmation` field |

#### 5.3 — Displaying validation errors in Blade

```blade
<input type="text"
       name="email"
       class="form-control @error('email') is-invalid @enderror"
       value="{{ old('email') }}">

@error('email')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

> [!NOTE]
> `old('email')` repopulates the input with the previously submitted value after a validation failure, so the user doesn't have to retype everything.

---

### 6. Environment & Configuration Problems

#### 6.1 — Changes to `.env` not taking effect

Laravel caches configuration. After editing `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
```

#### 6.2 — "Unable to locate file in Vite manifest"

Your frontend assets haven't been compiled:

```bash
npm run build        # One-time production build
# or
npm run dev          # Development server with hot reload
```

#### 6.3 — Clearing all caches at once

```bash
php artisan optimize:clear
```

This clears route cache, view cache, config cache, event cache, and compiled files.

---

### 7. Frontend & Blade Problems

#### 7.1 — Using named routes in Blade

```blade
{{-- ✅ Always prefer named routes --}}
<a href="{{ route('employees.show', $employee) }}">View</a>
<a href="{{ route('employees.edit', $employee) }}">Edit</a>
<form action="{{ route('employees.update', $employee) }}" ...>

{{-- ❌ Avoid hardcoded URLs --}}
<a href="/employees/{{ $employee->id }}">View</a>
```

#### 7.2 — Checking the current route for active nav links

```blade
<a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
   href="{{ route('employees.index') }}">
    Dashboard
</a>
```

#### 7.3 — Preserving query parameters in pagination

```php
// In the controller:
$employees = $query->paginate(10)->withQueryString();
//                                 ^^^^^^^^^^^^^^^^
// This ensures page links keep ?search=Alice&department_id=2
```

---

### 8. Debugging Workflow Checklist

When something doesn't work, follow this systematic approach:

```
┌─────────────────────────────────────────────────┐
│           Laravel Debugging Checklist           │
├─────────────────────────────────────────────────┤
│                                                 │
│  1. READ THE ERROR MESSAGE                      │
│     → What type? (404, 405, 419, 500, etc.)     │
│     → What file and line number?                │
│                                                 │
│  2. CHECK THE LOGS                              │
│     → storage/logs/laravel.log (scroll to end)  │
│     → Browser DevTools Console & Network tab    │
│                                                 │
│  3. INSPECT THE ROUTE                           │
│     → php artisan route:list --path=xxx         │
│     → Does the URL match? Correct HTTP verb?    │
│                                                 │
│  4. INSPECT THE CONTROLLER                      │
│     → Is the method defined?                    │
│     → Correct view name? Correct redirect?      │
│     → Is data being saved? (save/update/create) │
│                                                 │
│  5. INSPECT THE VIEW                            │
│     → Correct form method? @csrf? @method()?    │
│     → Input name matches controller key?        │
│     → Button type="submit"?                     │
│                                                 │
│  6. CHECK .env                                  │
│     → DB_CONNECTION, APP_URL, SESSION_DRIVER    │
│     → Run: php artisan config:clear             │
│                                                 │
│  7. RUN TESTS                                   │
│     → php artisan test --filter=test_name       │
│     → Tests isolate the problem from the UI     │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### HTTP Error Code Quick Reference

| Code | Meaning | Most Likely Cause |
|---|---|---|
| **404** | Not Found | URL doesn't match any route — check spelling, plural/singular |
| **405** | Method Not Allowed | Wrong HTTP verb — missing `@method('PUT')` or `@method('DELETE')` |
| **419** | Page Expired | Missing `@csrf` token in the form |
| **422** | Unprocessable Entity | Validation failed (API responses) |
| **500** | Server Error | PHP error — check `storage/logs/laravel.log` for the stack trace |

#### Useful Artisan Commands

```bash
# Routes
php artisan route:list                    # Show all routes
php artisan route:list --path=employees   # Filter by path
php artisan route:list --name=employees   # Filter by name

# Database
php artisan migrate                       # Run pending migrations
php artisan migrate:fresh --seed          # Reset DB and re-seed
php artisan tinker                        # Interactive PHP shell

# Cache clearing
php artisan config:clear                  # Clear config cache
php artisan view:clear                    # Clear compiled views
php artisan route:clear                   # Clear route cache
php artisan optimize:clear                # Clear everything

# Testing
php artisan test                          # Run all tests
php artisan test --filter=test_name       # Run a single test
php artisan test --compact                # Compact output

# Code quality
vendor/bin/pint --format agent            # Format PHP code
php -l path/to/file.php                   # Check for syntax errors
```

---

> [!TIP]
> **Save this file.** Whenever you encounter a bug in a Laravel project, scan Part 2 by error type. Nine times out of ten, the answer is in one of these patterns.
