# PHPStan Setup Guide

## Overview

PHPStan is a static analysis tool for PHP that helps find bugs in your code without running it. This guide explains how to install and configure PHPStan for the MASSAR ERP project.

## Installation

### Step 1: Install PHPStan via Composer

```bash
composer require --dev phpstan/phpstan
```

### Step 2: Install Laravel-specific PHPStan Extension (Recommended)

```bash
composer require --dev larastan/larastan
```

## Configuration

### Step 1: Create phpstan.neon Configuration File

Create a `phpstan.neon` file in the project root:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 5
    paths:
        - app
        - config
        - database
        - routes
        - tests
    
    # Exclude vendor and generated files
    excludePaths:
        - app/Console/Kernel.php
        - app/Http/Kernel.php
        - bootstrap/cache/*
        - storage/*
        - vendor/*
        - node_modules/*
    
    # Ignore specific errors (customize as needed)
    ignoreErrors:
        - '#Unsafe usage of new static#'
    
    # Check missing types
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
```

### Step 2: Create phpstan-baseline.neon (Optional)

If you have existing code with many errors, create a baseline:

```bash
vendor/bin/phpstan analyse --generate-baseline
```

This creates a `phpstan-baseline.neon` file that ignores existing errors, allowing you to focus on new code.

## Running PHPStan

### Analyze All Code

```bash
vendor/bin/phpstan analyse
```

### Analyze Specific Files

```bash
vendor/bin/phpstan analyse app/Services/RecalculationServiceHelper.php
```

### Analyze Specific Directories

```bash
vendor/bin/phpstan analyse app/Services
```

### Run with Different Levels

PHPStan has 10 levels (0-9), where higher levels are stricter:

```bash
# Level 0 - Basic checks
vendor/bin/phpstan analyse --level=0

# Level 5 - Recommended (good balance)
vendor/bin/phpstan analyse --level=5

# Level 9 - Strictest
vendor/bin/phpstan analyse --level=9
```

## Recommended Levels

- **Level 0-2**: Basic checks (undefined variables, unknown classes)
- **Level 3-5**: Recommended for most projects (type checking, return types)
- **Level 6-9**: Strict type checking (requires comprehensive type hints)

For the MASSAR ERP project, we recommend **Level 5** as a good balance between strictness and practicality.

## Integration with CI/CD

Add PHPStan to your CI/CD pipeline:

### GitHub Actions

```yaml
- name: Run PHPStan
  run: vendor/bin/phpstan analyse --error-format=github
```

### GitLab CI

```yaml
phpstan:
  script:
    - vendor/bin/phpstan analyse
```

## Common Issues and Solutions

### Issue: Too Many Errors

**Solution**: Start with a lower level and gradually increase:

```bash
# Start with level 0
vendor/bin/phpstan analyse --level=0

# Fix errors, then increase to level 1
vendor/bin/phpstan analyse --level=1

# Continue increasing until you reach level 5
```

Or create a baseline:

```bash
vendor/bin/phpstan analyse --generate-baseline
```

### Issue: False Positives

**Solution**: Add specific errors to `ignoreErrors` in `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        - '#Call to an undefined method App\\Models\\User::someMethod\(\)#'
```

### Issue: Memory Limit

**Solution**: Increase PHP memory limit:

```bash
php -d memory_limit=1G vendor/bin/phpstan analyse
```

### Issue: Slow Analysis

**Solution**: Analyze only changed files or specific directories:

```bash
# Analyze only Services directory
vendor/bin/phpstan analyse app/Services
```

## PHPStan for Average Cost Recalculation System

### Recommended Command

```bash
vendor/bin/phpstan analyse \
    app/Services/Validation/RecalculationInputValidator.php \
    app/Services/Monitoring/RecalculationPerformanceMonitor.php \
    app/Services/Config/RecalculationConfigManager.php \
    app/Services/Manufacturing/ManufacturingChainHandler.php \
    app/Services/Consistency/AverageCostConsistencyChecker.php \
    app/Services/RecalculationServiceHelper.php \
    app/Services/RecalculationServiceFactory.php \
    app/Services/AverageCostRecalculationServiceOptimized.php \
    app/Jobs/RecalculateAverageCostJob.php \
    app/Console/Commands/CheckAverageCostConsistency.php \
    app/Console/Commands/FixAverageCostInconsistencies.php \
    --level=5
```

### Expected Results

All files in the average cost recalculation system should pass PHPStan level 5 analysis with:
- ✅ Proper type hints on all methods
- ✅ Correct return types
- ✅ No undefined variables
- ✅ No undefined methods
- ✅ Proper PHPDoc blocks

## Benefits of Using PHPStan

1. **Early Bug Detection**: Find bugs before they reach production
2. **Better Code Quality**: Enforce type safety and best practices
3. **Improved Documentation**: Requires proper type hints and PHPDoc
4. **Refactoring Confidence**: Catch breaking changes during refactoring
5. **Team Consistency**: Enforce coding standards across the team

## Next Steps

1. Install PHPStan and Larastan
2. Create `phpstan.neon` configuration
3. Run analysis on new code
4. Fix any issues found
5. Integrate into CI/CD pipeline
6. Gradually increase strictness level

## Resources

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Larastan Documentation](https://github.com/larastan/larastan)
- [PHPStan Rule Levels](https://phpstan.org/user-guide/rule-levels)
- [PHPStan Configuration Reference](https://phpstan.org/config-reference)

## Conclusion

PHPStan is a valuable tool for maintaining code quality in the MASSAR ERP project. While not currently installed, following this guide will help you set it up and integrate it into your development workflow.
