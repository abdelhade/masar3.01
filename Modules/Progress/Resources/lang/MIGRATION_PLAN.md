# Translation Files Migration Plan

## Current Status

### Files Created ✅
1. `common.php` (EN/AR) - Common translations
2. `projects_new.php` (EN/AR) - Project-specific translations
3. `daily_progress.php` (EN/AR) - Daily progress translations
4. `README.md` - Documentation

### Existing Files (To Keep)
- `employees.php` (EN/AR) - Already well organized
- `activity-logs.php` (EN/AR)
- `auth.php` (EN/AR)
- `pagination.php` (EN/AR)
- `passwords.php` (EN/AR)
- `validation.php` (EN/AR)

### Files to Backup and Eventually Replace
- `general.php` (EN/AR) - Contains 1000+ keys with duplicates
- `projects.php` (EN/AR) - Will be replaced by `projects_new.php`

## Migration Steps

### Phase 1: Backup (CURRENT PHASE)
```bash
# Backup old files
cp Modules/Progress/Resources/lang/en/general.php Modules/Progress/Resources/lang/en/general.php.backup
cp Modules/Progress/Resources/lang/ar/general.php Modules/Progress/Resources/lang/ar/general.php.backup
cp Modules/Progress/Resources/lang/en/projects.php Modules/Progress/Resources/lang/en/projects.php.backup
cp Modules/Progress/Resources/lang/ar/projects.php Modules/Progress/Resources/lang/ar/projects.php.backup
```

### Phase 2: Code Migration
Update all Blade files and PHP files to use new translation keys:

#### Example Migrations:

**Common Terms:**
```php
// Old
__('progress::general.save')
__('progress::general.cancel')
__('progress::general.delete')

// New
__('progress::common.save')
__('progress::common.cancel')
__('progress::common.delete')
```

**Project Terms:**
```php
// Old
__('progress::projects.name')
__('progress::general.project_name')

// New
__('progress::projects.project_name')
```

**Daily Progress Terms:**
```php
// Old
__('progress::general.add_progress')
__('progress::general.daily_progress')

// New
__('progress::daily_progress.add_progress')
__('progress::daily_progress.title')
```

### Phase 3: Testing
1. Test all pages in the Progress module
2. Verify all translations appear correctly
3. Check both English and Arabic languages
4. Ensure no missing translation keys

### Phase 4: Cleanup
Once all code is migrated and tested:
1. Rename `projects_new.php` to `projects.php`
2. Remove old `general.php` files
3. Remove backup files

## Files to Search and Update

### Blade Files
```bash
# Find all Blade files using progress translations
grep -r "progress::" Modules/Progress/Resources/views/
```

### PHP Files
```bash
# Find all PHP files using progress translations
grep -r "__('progress::" Modules/Progress/
```

### JavaScript Files
```bash
# Find any JS files that might reference translations
grep -r "progress::" public/js/
```

## Translation Key Mapping

### Common Keys (general.php → common.php)
| Old Key | New Key |
|---------|---------|
| `general.save` | `common.save` |
| `general.cancel` | `common.cancel` |
| `general.delete` | `common.delete` |
| `general.edit` | `common.edit` |
| `general.view` | `common.view` |
| `general.create` | `common.create` |
| `general.update` | `common.update` |
| `general.back` | `common.back` |
| `general.search` | `common.search` |
| `general.filter` | `common.filter` |
| `general.export` | `common.export` |
| `general.print` | `common.print` |
| `general.loading` | `common.loading` |
| `general.status` | `common.status` |
| `general.active` | `common.active` |
| `general.pending` | `common.pending` |
| `general.completed` | `common.completed` |

### Project Keys (general.php → projects.php)
| Old Key | New Key |
|---------|---------|
| `general.project_name` | `projects.project_name` |
| `general.start_date` | `projects.start_date` |
| `general.end_date` | `projects.end_date` |
| `general.project_type` | `projects.project_type` |
| `general.working_zone` | `projects.working_zone` |
| `general.client` | `projects.client` |
| `general.work_items` | `projects.work_items` |
| `general.daily_progress` | `projects.daily_progress` |

### Daily Progress Keys (general.php → daily_progress.php)
| Old Key | New Key |
|---------|---------|
| `general.add_progress` | `daily_progress.add_progress` |
| `general.planned_qty` | `daily_progress.planned_qty` |
| `general.actual_qty` | `daily_progress.actual_qty` |
| `general.qty_actual_today` | `daily_progress.qty_actual_today` |

## Automated Migration Script

Create a script to help with migration:

```php
<?php
// migration_helper.php

$mappings = [
    // Common mappings
    "progress::general.save" => "progress::common.save",
    "progress::general.cancel" => "progress::common.cancel",
    "progress::general.delete" => "progress::common.delete",
    // ... add more mappings
];

function migrateFile($filePath, $mappings) {
    $content = file_get_contents($filePath);
    
    foreach ($mappings as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
    
    file_put_contents($filePath, $content);
    echo "Migrated: $filePath\n";
}

// Usage:
// php migration_helper.php
```

## Rollback Plan

If issues occur during migration:

1. Restore backup files:
```bash
cp Modules/Progress/Resources/lang/en/general.php.backup Modules/Progress/Resources/lang/en/general.php
cp Modules/Progress/Resources/lang/ar/general.php.backup Modules/Progress/Resources/lang/ar/general.php
```

2. Revert code changes using Git:
```bash
git checkout -- Modules/Progress/
```

## Timeline

- **Week 1**: Create new translation files ✅ (DONE)
- **Week 2**: Update Blade templates
- **Week 3**: Update PHP controllers and classes
- **Week 4**: Testing and bug fixes
- **Week 5**: Cleanup and documentation

## Notes

- Keep backward compatibility during migration
- Test thoroughly before removing old files
- Document any custom translation keys
- Update team about new structure

## Contact

For questions about migration, contact the development team.
