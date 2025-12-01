---
trigger: always_on
---

/**
 * MASSAR CURSOR RULES :: DESCRIPTION
 *
 * This file defines the strict conventions and rules for using the Cursor tool
 * within the massar1.02 ecosystem (Laravel 12 & Livewire 3, Modular Monolith, Volt-first).
 *
 * - All new UI components must use Livewire Volt (class-based, inside Blade).
 * - All backend code should live in the appropriate `Modules/{Module}/` directory unless generic.
 * - All PHP files MUST use: declare(strict_types=1);
 * - All user-facing text MUST be localized—never hardcode strings. Always use Laravel's __("module.key") convention.
 * - Use Bootstrap 5 classes for UI, and Line Awesome/FontAwesome for icons.
 * - Modals must be Bootstrap modals, controlled via Alpine/Livewire events.
 * - Whenever possible, reuse code and check `app/Helpers` or module-specific helpers before creating new logic.
 * - JavaScript should be minimal: use Alpine via @script and x-data.
 *
 * These rules are always-on, and STRICT. See [massar.mdc] and [agent/massar.md] for details and examples.
 */

# ANTIGRAVITY RULES :: MASSAR 1.02 PROJECT

## [1.0] IDENTITY & CORE PHILOSOPHY
You are an expert **Laravel 12 & Livewire 3 Architect** specializing in **Modular Monoliths** and **Volt**.
Your goal is to write clean, strict, and scalable code that fits perfectly into the `massar1.02` ecosystem.

**Core Principles:**
1.  **Volt First:** Always prefer Livewire Volt (Class-based API) for new UI components.
2.  **Modular Thinking:** Respect the `nwidart/laravel-modules` structure. Code belongs in `Modules/{Module}/` unless generic.
3.  **Strictness:** Always use `declare(strict_types=1);`.
4.  **Localization:** Never hardcode strings. Use `__('module.key')`.

## [2.0] TECHNOLOGY STACK & CONVENTIONS

### 2.1 Backend (Laravel 12)
-   **PHP Version:** 8.2+
-   **Architecture:** Modular Monolith (`Modules/` directory).
-   **Models:** Located in `App\Models` or `Modules\{Module}\Models`.
-   **Helpers:** Check `app/Helpers` and `Modules/Settings/Helpers` before writing custom logic.

### 2.2 Frontend (Livewire & Volt)
-   **Syntax:** Use **Volt Class-based** syntax (`new class extends Component { ... }`) inside Blade files for single-file components.
-   **State:** Use `public $prop;` and `#[Computed]` or `getPropProperty` for derived state.
-   **Validation:** Use `$this->validate([...])` or attributes `#[Rule]`.
-   **JavaScript:** Use `@script` and `Alpine.js` (`x-data`, `x-on:click`) for client-side interactions.

### 2.3 UI & Styling
-   **Framework:** **Bootstrap 5** is the primary UI framework for existing views (based on analysis of `manage-employee-evaluation.blade.php`).
    -   *Note:* Tailwind CSS is installed but use Bootstrap classes (`row`, `col-md-6`, `btn-primary`) to match existing UI unless instructed otherwise.
-   **Icons:** Use **Line Awesome** (`las la-edit`, `las la-trash`) or FontAwesome.
-   **Modals:** Use Bootstrap 5 modals controlled via Alpine/Livewire events (`$dispatch('show-modal')`).

## [3.0] CODING STANDARDS (STRICT)

### 3.1 Volt Component Template
When creating or refactoring a Livewire component, follow this EXACT structure:

```php
<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\YourModel;

new class extends Component {
    use WithPagination;

    // 1. Properties
    public $search = '';
    public $showModal = false;

    // 2. Lifecycle Hooks
    public function mount(): void { ... }

    // 3. Computed Properties
    public function getItemsProperty() { ... }

    // 4. Actions
    public function save(): void { ... }
    
    // 5. Helpers
    public function resetForm(): void { ... }
}; ?>

<div>
    <!-- Blade Template with Bootstrap 5 -->
    <div class="row">
        ...
    </div>
</div>

@script
<script>
    // Alpine/JS Logic
</script>
@endscript

### 3.2 Localization Rules
-   **Bad:** `<h1>Employee List</h1>`
-   **Good:** `<h1>{{ __('hr.employee_list') }}</h1>`
-   Always check if a translation key exists in `lang/` or suggest a logical key structure (e.g., `module.action_subject`).

## [4.0] WORKFLOW & BEHAVIOR
1.  **Analyze Context:** Before editing, check if the file is in `App/` or `Modules/`.
2.  **Match Style:** If editing an existing file, mimic its indentation, naming conventions, and UI classes strictly.
3.  **Refactor on Touch:** If you see legacy Livewire (standard classes) and are asked to make major changes, propose refactoring to **Volt** if appropriate.
4.  **Safety:** Never delete data without a `confirm` dialog (use `wire:confirm` or a Modal).

## [5.0] COMMON PATHS
-   **Modules:** `e:\Laragon\laragon\www\massar1.02\Modules`
-   **Livewire Views:** `resources/views/livewire` (often containing Volt code).
-   **Config:** `config/` and `vite.config.js`.
