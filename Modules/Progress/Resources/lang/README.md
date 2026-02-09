# Progress Module - Translation Files Structure

## Overview
This document describes the organized structure of translation files for the Progress module.

## File Structure

### 1. Common Translations (`common.php`)
Contains generic terms used across multiple contexts:
- Actions (view, edit, delete, create, etc.)
- Confirmations (yes, no, are you sure, etc.)
- Status (active, inactive, pending, etc.)
- Messages (loading, success, error, etc.)
- Data display (no data, showing, pagination, etc.)
- Common fields (name, description, date, etc.)
- Navigation (dashboard, home, settings, etc.)
- Days of week
- Time units

**Usage Example:**
```php
__('progress::common.save')
__('progress::common.confirm_delete')
__('progress::common.loading')
```

### 2. Projects (`projects.php` / `projects_new.php`)
Contains all project-specific translations:
- Page titles
- Basic information
- Client information
- Dates & duration
- Work schedule
- Status
- Work items
- Quantities & units
- Progress tracking
- Team management
- Templates
- Reports & charts
- Statistics
- Timeline
- Validation messages
- Drafts

**Usage Example:**
```php
__('progress::projects.create')
__('progress::projects.project_name')
__('progress::projects.start_date')
```

### 3. Daily Progress (`daily_progress.php`)
Contains translations specific to daily progress tracking:
- Page titles
- Form fields
- Quantity details
- Planned vs actual comparisons
- Actions
- Filters
- Export options

**Usage Example:**
```php
__('progress::daily_progress.add_progress')
__('progress::daily_progress.planned_qty')
__('progress::daily_progress.actual_qty')
```

### 4. Employees (`employees.php`)
Contains employee-specific translations:
- Management
- List
- Form fields (name, position, phone, email)
- Actions
- Password management

**Usage Example:**
```php
__('progress::employees.management')
__('progress::employees.create')
__('progress::employees.name')
```

### 5. Other Files
- `activity-logs.php` - Activity log translations
- `auth.php` - Authentication translations
- `pagination.php` - Pagination translations
- `passwords.php` - Password reset translations
- `validation.php` - Validation messages

## Migration Guide

### Old Structure (general.php)
The old `general.php` file contained over 1000 translation keys with many duplicates and poor organization.

### New Structure
Translation keys are now organized into logical files based on their context:

| Old File | New Files |
|----------|-----------|
| `general.php` | `common.php`, `projects_new.php`, `daily_progress.php` |

### How to Migrate Your Code

1. **For common terms** (save, delete, edit, etc.):
   ```php
   // Old
   __('progress::general.save')
   
   // New
   __('progress::common.save')
   ```

2. **For project-specific terms**:
   ```php
   // Old
   __('progress::general.project_name')
   
   // New
   __('progress::projects.project_name')
   ```

3. **For daily progress terms**:
   ```php
   // Old
   __('progress::general.add_progress')
   
   // New
   __('progress::daily_progress.add_progress')
   ```

## Benefits of New Structure

1. **Better Organization**: Each file has a clear purpose
2. **No Duplicates**: Each translation key appears only once
3. **Easier Maintenance**: Finding and updating translations is simpler
4. **Better Performance**: Smaller files load faster
5. **Clear Context**: Translation keys are grouped by their usage context
6. **Scalability**: Easy to add new translation files for new features

## Recommendations

1. **Use the new files** (`common.php`, `projects_new.php`, `daily_progress.php`) for all new code
2. **Gradually migrate** existing code from `general.php` to the new structure
3. **Keep `general.php`** temporarily for backward compatibility
4. **Remove `general.php`** once all code has been migrated

## Translation Key Naming Convention

Follow these conventions when adding new translation keys:

1. **Use descriptive names**: `project_name` instead of `name`
2. **Use snake_case**: `start_date` not `startDate` or `StartDate`
3. **Group related keys**: All status-related keys should start with `status_`
4. **Be specific**: `confirm_delete_project` instead of just `confirm`
5. **Avoid abbreviations**: `quantity` instead of `qty` (except for display text)

## File Maintenance

When adding new translations:

1. **Choose the right file**: Determine which file best fits the context
2. **Check for duplicates**: Search existing files before adding new keys
3. **Add comments**: Group related keys with comments
4. **Keep alphabetical order**: Within each section, maintain alphabetical order
5. **Update both languages**: Always add translations for both Arabic and English

## Support

For questions or issues with translations, please contact the development team.
