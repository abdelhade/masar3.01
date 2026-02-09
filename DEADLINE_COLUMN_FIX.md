# Fix: Issues Module - Deadline Column Error

## Problem 1: Deadline Column
The Issues module was throwing a database error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'deadline' in 'where clause'
```

## Root Cause
- **Database migration** created column: `due_date`
- **Model and Controller** were using: `deadline`
- This mismatch caused SQL errors when querying or filtering issues

## Solution
Updated all references from `deadline` to `due_date` to match the database schema:

### 1. Model Changes (`Modules/Progress/Models/Issue.php`)
- ✅ Updated `$fillable` array: `'deadline'` → `'due_date'`
- ✅ Updated `$casts` array: `'deadline' => 'date'` → `'due_date' => 'date'`
- ✅ Updated `scopeDeadlineApproaching()` method to use `due_date` column
- ✅ Updated `isOverdue()` method to use `$this->due_date` property

### 2. Controller Changes (`Modules/Progress/Http/Controllers/IssueController.php`)
- ✅ Line 75-76: `where('deadline', '>=', ...)` → `where('due_date', '>=', ...)`
- ✅ Line 79-80: `where('deadline', '<=', ...)` → `where('due_date', '<=', ...)`
- ✅ Line 455-458: `whereNotNull('deadline')` → `whereNotNull('due_date')`

### 3. View Changes
- ✅ `show.blade.php`: `$issue->deadline` → `$issue->due_date`
- ✅ `index.blade.php`: `$issue->deadline` → `$issue->due_date`
- ✅ `kanban.blade.php`: `$issue->deadline` → `$issue->due_date`
- ✅ `edit.blade.php`: `$issue->deadline` → `$issue->due_date`

### 4. Form Request Changes
- ✅ Added `prepareForValidation()` method in both `StoreIssueRequest` and `UpdateIssueRequest`
- ✅ This method maps the form input `deadline` to `due_date` for database compatibility
- ✅ Keeps the form field name as `deadline` (user-facing) while storing as `due_date` (database)

---

## Problem 2: Module and Reproduce Steps Columns
The Issues module was throwing another database error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'module' in 'field list'
```

## Root Cause
- The original migration (`2025_07_01_000008_create_issues_table.php`) did not include `module` and `reproduce_steps` columns
- However, the Model, Controller, Views, and Form Requests all expected these columns to exist
- This caused SQL errors when trying to query, filter, or save issues

## Solution
Created a new migration to add the missing columns:

### Migration: `2026_02_07_132304_add_module_and_reproduce_steps_to_issues_table.php`
```php
Schema::table('issues', function (Blueprint $table) {
    $table->string('module', 255)->nullable()->after('assigned_to');
    $table->text('reproduce_steps')->nullable()->after('description');
});
```

### Changes Made:
- ✅ Added `module` column (varchar 255, nullable) - for categorizing issues by module/component
- ✅ Added `reproduce_steps` column (text, nullable) - for documenting how to reproduce the issue
- ✅ Both columns placed in logical positions within the table structure
- ✅ Migration executed successfully on database `kon3`

---

## Files Modified

### Problem 1 (Deadline → Due Date):
1. `Modules/Progress/Models/Issue.php`
2. `Modules/Progress/Http/Controllers/IssueController.php`
3. `Modules/Progress/Http/Requests/StoreIssueRequest.php`
4. `Modules/Progress/Http/Requests/UpdateIssueRequest.php`
5. `Modules/Progress/Resources/views/issues/show.blade.php`
6. `Modules/Progress/Resources/views/issues/index.blade.php`
7. `Modules/Progress/Resources/views/issues/kanban.blade.php`
8. `Modules/Progress/Resources/views/issues/edit.blade.php`

### Problem 2 (Missing Columns):
1. `Modules/Progress/database/migrations/2026_02_07_132304_add_module_and_reproduce_steps_to_issues_table.php` (NEW)

---

## Testing
After these fixes:
- ✅ Issues index page loads without errors
- ✅ Filtering by deadline (from/to) works correctly
- ✅ Filtering by module works correctly
- ✅ Creating/editing issues with deadline, module, and reproduce_steps saves properly
- ✅ Overdue issues statistics calculate correctly
- ✅ Kanban board displays deadlines correctly
- ✅ Module field displays and filters correctly

---

## Final Database Schema
The `issues` table now has the following structure:
```sql
- id (bigint)
- project_id (bigint)
- title (varchar)
- description (text)
- reproduce_steps (text) ← ADDED
- priority (enum: low, medium, high, critical)
- status (enum: open, in_progress, resolved, closed)
- reported_by (bigint)
- assigned_to (bigint)
- module (varchar 255) ← ADDED
- due_date (date) ← Was being called 'deadline' in code
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp)
```

---

## Commits
1. **Fix: Replace 'deadline' with 'due_date' in Issues module**
   - Updated Issue model to use 'due_date' column instead of 'deadline'
   - Fixed IssueController queries to use 'due_date'
   - Updated all Blade views to display 'due_date' property
   - Added prepareForValidation() in form requests to map 'deadline' input to 'due_date'
   - Resolves SQLSTATE[42S22] Column not found error for 'deadline' column

2. **Fix: Add missing 'module' and 'reproduce_steps' columns to issues table**
   - Created migration to add module (varchar 255) and reproduce_steps (text) columns
   - Resolves SQLSTATE[42S22] Column not found error for 'module' column
   - Both columns are nullable and placed in logical positions in table structure

---

## Status
✅ **FIXED** - All changes committed and pushed to branch `hadi`
✅ **TESTED** - Database columns verified and migration executed successfully
