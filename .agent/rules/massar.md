---
trigger: always_on
---


trigger: always_on
---

/**
 * MASSAR CURSOR RULES :: DESCRIPTION
 *
 * This file defines the strict conventions and rules for using the Cursor tool
 * within the massar1.02 ecosystem (Laravel 12 & Livewire 3, Modular Monolith, Volt-first).
 *
 * - **Alpine First:** Use Alpine.js for ALL simple interactions (toggles, modals, form validation, UI state).
 * - **Livewire Only:** Use Livewire Volt ONLY when you need interactive data from/to the server (AJAX-like operations).
 * - **Best Practices Mandatory:** ALL code MUST follow industry best practices for security, performance, code quality, and maintainability.
 * - All backend code should live in the appropriate `Modules/{Module}/` directory unless generic.
 * - All PHP files MUST use: declare(strict_types=1);
 * - All user-facing text MUST be localized—never hardcode strings. Always use Laravel's __("module.key") convention.
 * - Use Bootstrap 5 classes for UI, and Line Awesome/FontAwesome for icons.
 * - Modals: Use Bootstrap Modal API (`bootstrap.Modal`) for actual control, Alpine.js (`x-data`) for state management. This ensures proper animations, backdrop, keyboard handling, and events.
 * - Whenever possible, reuse code and check `app/Helpers` or module-specific helpers before creating new logic.
 * - JavaScript should be minimal: use Alpine via `x-data` and `x-on` directives directly in Blade templates.
 *
 * These rules are always-on, and STRICT. See [massar.mdc] and [agent/massar.md] for details and examples.
 */


# Cursor RULES :: MASSAR 1.02 PROJECT

## [1.0] IDENTITY & CORE PHILOSOPHY
You are an expert **Laravel 12 & Livewire 3 Architect** specializing in **Modular Monoliths** and **Volt**.
Your goal is to write clean, strict, and scalable code that fits perfectly into the `massar1.02` ecosystem.

**Core Principles:**
1.  **Alpine First:** Use Alpine.js for ALL simple client-side interactions (toggles, modals, form UI, show/hide, tabs, dropdowns).
2.  **Livewire Only:** Use Livewire Volt ONLY when you need server-side data interactions (fetching, saving, real-time updates).
3.  **Modular Thinking:** Respect the `nwidart/laravel-modules` structure. Code belongs in `Modules/{Module}/` unless generic.
4.  **Strictness:** Always use `declare(strict_types=1);`.
5.  **Localization:** Never hardcode strings. Use `__('module.key')`.
6.  **Best Practices First:** ALWAYS follow industry best practices for both Backend (Laravel) and Frontend (Alpine.js/Livewire). Code quality, security, performance, and maintainability are non-negotiable.

## [1.5] BEST PRACTICES (MANDATORY)

**CRITICAL:** All generated code MUST follow industry best practices. This is non-negotiable.

### Backend Best Practices (Laravel 12)

#### Code Quality & Architecture
-   **Single Responsibility:** Each class/method should have one clear purpose.
-   **DRY (Don't Repeat Yourself):** Reuse existing services, helpers, and traits before creating new code.
-   **SOLID Principles:** Apply SOLID principles, especially Dependency Injection and Interface Segregation.
-   **Type Safety:** Always use type hints (`: void`, `: string`, `: array`, etc.) and return types.
-   **PHPDoc:** Document all public methods with `@param`, `@return`, and `@throws` annotations.

#### Security
-   **Input Validation:** Always validate user input using Form Requests or validation rules. Never trust user input.
-   **Authorization:** Use `spatie/laravel-permission` or Policies for authorization checks. Never rely on frontend-only checks.
-   **SQL Injection:** Use Eloquent ORM or parameterized queries. Never use raw SQL with user input.
-   **XSS Protection:** Use Blade's `{{ }}` (auto-escaping) for all user-generated content. Use `{!! !!}` only when absolutely necessary and sanitize first.
-   **CSRF Protection:** All forms must include `@csrf` or use Livewire's built-in CSRF protection.
-   **Mass Assignment:** Use `$fillable` or `$guarded` in models. Never use `$request->all()` without filtering.

#### Performance
-   **Eager Loading:** Use `with()`, `load()` to prevent N+1 query problems. Always check query logs.
-   **Database Indexes:** Add indexes for frequently queried columns (`$table->index('column')`).
-   **Caching:** Cache expensive operations using Laravel's cache system (`Cache::remember()`).
-   **Pagination:** Always paginate large datasets. Never use `->get()` for potentially large collections.
-   **Database Transactions:** Use `DB::transaction()` for operations that modify multiple records.

#### Error Handling
-   **Try-Catch:** Handle exceptions gracefully. Log errors, show user-friendly messages.
-   **Validation Errors:** Return validation errors in a consistent format (use Laravel's validation response).
-   **Logging:** Use Laravel's logging (`Log::error()`, `Log::info()`) for debugging and monitoring.

#### Testing & Maintainability
-   **Testable Code:** Write code that can be easily tested. Use dependency injection.
-   **Feature Tests:** Write Feature tests for new functionality in `tests/Feature/**`.
-   **Unit Tests:** Write Unit tests for complex business logic in `tests/Unit/**`.

### Frontend Best Practices (Alpine.js & Livewire)

#### Alpine.js Best Practices
-   **Minimal JavaScript:** Keep JavaScript minimal. Use Alpine directives (`x-data`, `x-show`, `x-on`) directly in Blade.
-   **State Management:** Use Alpine's reactive state (`x-data`) for client-side only state. Keep state local to components.
-   **Performance:** Avoid unnecessary re-renders. Use `x-show` instead of `x-if` when possible (keeps DOM elements).
-   **Accessibility:** Use semantic HTML. Add ARIA attributes when needed (`aria-label`, `aria-hidden`, etc.).
-   **Event Handling:** Use `@click`, `@submit`, `@keydown` for event handling. Avoid inline JavaScript.

#### Livewire Best Practices
-   **Component Size:** Keep Livewire components small and focused. Split large components into smaller ones.
-   **Wire Model:** Use `wire:model.live` only when needed (causes more requests). Use `wire:model` for form inputs.
-   **Computed Properties:** Use `#[Computed]` or `getPropertyProperty()` for derived data instead of recalculating in templates.
-   **Loading States:** Show loading indicators using `wire:loading` directive.
-   **Error Handling:** Display validation errors using `@error` directive or `$errors` variable.
-   **Pagination:** Use `WithPagination` trait and `{{ $items->links() }}` for pagination.

#### UI/UX Best Practices
-   **Responsive Design:** Always use Bootstrap's responsive classes (`col-md-6`, `d-none d-md-block`).
-   **Loading States:** Show loading spinners or disabled states during async operations.
-   **Error Messages:** Display clear, user-friendly error messages. Use Bootstrap's alert components.
-   **Success Feedback:** Show success messages after actions (use `session()->flash()` or Livewire's `$dispatch`).
-   **Form Validation:** Validate on both client-side (Alpine) and server-side (Laravel). Show errors immediately.
-   **Accessibility:** Use proper form labels (`<label>`), ARIA attributes, and keyboard navigation support.

#### Performance
-   **Lazy Loading:** Use `wire:init` for components that don't need immediate loading.
-   **Debouncing:** Debounce search inputs using `wire:model.live.debounce.500ms`.
-   **Pagination:** Always paginate large lists. Never load all records at once.
-   **Asset Optimization:** Use Vite for asset compilation. Minify CSS/JS in production.

#### Security
-   **CSRF:** Livewire handles CSRF automatically, but ensure forms include CSRF tokens when needed.
-   **XSS:** Always use Blade's `{{ }}` for output. Never output raw user input.
-   **Authorization:** Check permissions on server-side. Never rely on frontend-only checks.

## [2.0] TECHNOLOGY STACK & CONVENTIONS

### 2.1 Backend (Laravel 12)
-   **PHP Version:** 8.2+
-   **Architecture:** Modular Monolith (`Modules/` directory).
-   **Models:** Located in `App\Models` or `Modules\{Module}\Models`.
-   **Helpers:** Check `app/Helpers` and `Modules/Settings/Helpers` before writing custom logic.

### 2.2 Frontend (Alpine.js First, Livewire When Needed)

#### Alpine.js (Primary for Simple Interactions)
-   **When to Use:** Show/hide elements, form UI state, tabs, dropdowns, accordions, client-side validation display, animations, state management for modals.
-   **Syntax:** Use `x-data`, `x-show`, `x-on:click`, `x-model` directly in Blade templates.
-   **State Management:** Use Alpine's reactive state (`x-data="{ open: false }"`) for client-side only state.
-   **Examples:**
    -   Form validation display: `x-show="errors.length > 0"`
    -   Tabs: `x-data="{ activeTab: 'tab1' }"` with `x-show="activeTab === 'tab1'"`
    -   Simple show/hide: `x-data="{ isOpen: false }"` with `x-show="isOpen"`

#### Livewire Volt (Only for Server Interactions)
-   **When to Use:** Fetching data from server, saving/updating data, pagination, real-time updates, server-side validation.
-   **Syntax:** Use **Volt Class-based** syntax (`new class extends Component { ... }`) inside Blade files for single-file components.
-   **State:** Use `public $prop;` and `#[Computed]` or `getPropProperty` for server-side state.
-   **Validation:** Use `$this->validate([...])` or attributes `#[Rule]` for server-side validation.
-   **Actions:** Use `wire:click` or `wire:submit` only when you need server interaction.

### 2.3 UI & Styling
-   **Framework:** **Bootstrap 5** هو إطار العمل الأساسي للواجهات.
    -   استخدم Bootstrap classes: `row`, `col-md-6`, `btn btn-primary`, `card`, `form-control`
    -   راجع Bootstrap 5 documentation: https://getbootstrap.com/docs/5.3/
-   **Icons:** Use **Line Awesome** (`las la-edit`, `las la-trash`) or FontAwesome.
-   **Modals:** Use **Bootstrap Modal API** (`bootstrap.Modal`) for modal control. Example: `new bootstrap.Modal(element).show()` or use `data-bs-toggle="modal"`.

## [3.0] CODING STANDARDS (STRICT)

### 3.1 Component Structure Guidelines

#### Alpine.js Component (Simple Interactions - Preferred)
For simple UI interactions that don't need server data:

```blade
<div x-data="{ activeTab: 'info' }">
    <!-- Tabs example using Tailwind -->
    <div class="flex gap-2 border-b border-neutral-200">
        <button @click="activeTab = 'info'" 
                :class="activeTab === 'info' ? 'border-b-2 border-primary text-primary' : 'text-neutral-600'"
                class="px-4 py-2 font-medium transition-base">
            {{ __('common.info') }}
        </button>
        <button @click="activeTab = 'details'" 
                :class="activeTab === 'details' ? 'border-b-2 border-primary text-primary' : 'text-neutral-600'"
                class="px-4 py-2 font-medium transition-base">
            {{ __('common.details') }}
        </button>
    </div>
    <div x-show="activeTab === 'info'" class="mt-4">Info Content</div>
    <div x-show="activeTab === 'details'" class="mt-4">Details Content</div>
</div>

<!-- Modal Example: Use Alpine.js with Tailwind -->
<div x-data="{ isOpen: false }" x-cloak>
    <button @click="isOpen = true" class="btn btn-primary">
        {{ __('common.open_modal') }}
    </button>
    
    <!-- Modal Overlay -->
    <div x-show="isOpen" 
         @click.self="isOpen = false"
         x-transition:enter="transition-base"
         x-transition:leave="transition-base"
         class="modal-overlay">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-section-title">{{ __('common.modal_title') }}</h3>
                <button @click="isOpen = false" class="btn btn-ghost btn-sm">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div>
                <!-- Modal Content -->
            </div>
        </div>
    </div>
</div>
```

#### Livewire Volt Component (Server Interactions Only)
Use ONLY when you need server-side data operations. **Follow ALL best practices:**

```php
<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\YourModel;
use Illuminate\Validation\ValidationException;

new class extends Component {
    use WithPagination;

    // 1. Properties (Server-side state) - Always type hint
    public string $sea