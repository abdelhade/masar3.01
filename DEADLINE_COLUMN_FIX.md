# Fix: Issues Module - Deadline Column Error

## Problem
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

## Files Modified
1. `Modules/Progress/Models/Issue.php`
2. `Modules/Progress/Http/Controllers/IssueController.php`
3. `Modules/Progress/Http/Requests/StoreIssueRequest.php`
4. `Modules/Progress/Http/Requests/UpdateIssueRequest.php`
5. `Modules/Progress/Resources/views/issues/show.blade.php`
6. `Modules/Progress/Resources/views/issues/index.blade.php`
7. `Modules/Progress/Resources/views/issues/kanban.blade.php`
8. `Modules/Progress/Resources/views/issues/edit.blade.php`

## Testing
After this fix:
- ✅ Issues index page should load without errors
- ✅ Filtering by deadline (from/to) should work correctly
- ✅ Creating/editing issues with deadline should save properly
- ✅ Overdue issues statistics should calculate correctly
- ✅ Kanban board should display deadlines correctly

## Database Schema
The `issues` table has the following structure:
```sql
- id (bigint)
- project_id (bigint)
- title (varchar)
- description (text)
- priority (enum: low, medium, high, critical)
- status (enum: open, in_progress, resolved, closed)
- reported_by (bigint)
- assigned_to (bigint)
- due_date (date) ← This is the correct column name
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp)
```

## Commit
```
Fix: Replace 'deadline' with 'due_date' in Issues module

- Updated Issue model to use 'due_date' column instead of 'deadline'
- Fixed IssueController queries to use 'due_date'
- Updated all Blade views to display 'due_date' property
- Added prepareForValidation() in form requests to map 'deadline' input to 'due_date'
- Resolves SQLSTATE[42S22] Column not found error for 'deadline' column
```

## Status
✅ **FIXED** - All changes committed and pushed to branch `hadi`
