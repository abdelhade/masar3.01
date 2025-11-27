# Tom Select Component Documentation

## Overview

The Tom Select component is a highly optimized, feature-rich dropdown component that integrates seamlessly with Laravel Livewire and Alpine.js. It provides advanced selection capabilities with RTL support, modal integration, comprehensive event handling, and automatic initialization within dynamic templates.

## Key Features

- **Livewire Integration**: Automatic synchronization with Livewire models
- **Alpine.js Compatibility**: Seamless integration with Alpine.js templates and reactive data
- **RTL Support**: Built-in right-to-left language support for Arabic
- **Modal Integration**: Automatic handling of modal open/close events
- **Performance Optimized**: Efficient initialization and cleanup with DOM mutation observer
- **Event Handling**: Comprehensive event system with custom handlers
- **Multiple Selection**: Support for single and multiple selections with remove buttons
- **Dynamic Templates**: Works within Alpine.js `x-if` templates and conditional rendering
- **Customizable**: Extensive configuration options and plugin system
- **Error Handling**: Robust error handling and logging with auto-recovery
- **Empty State Management**: Prevents auto-selection on initialization

## Basic Usage

### Simple Dropdown

```blade
<x-tom-select
    name="category"
    :options="$categories"
    wireModel="selectedCategory"
    placeholder="اختر فئة"
/>
```

### Multiple Selection

```blade
<x-tom-select
    name="tags"
    :options="$tags"
    wireModel="selectedTags"
    :multiple="true"
    placeholder="اختر العلامات"
/>
```

### With Search and Plugins

```blade
<x-tom-select
    name="employee"
    :options="$employees"
    wireModel="selectedEmployee"
    :search="true"
    placeholder="اختر موظف"
    :tomOptions="[
        'plugins' => [
            'dropdown_input' => ['class' => 'font-hold fw-bold font-14'],
            'remove_button' => ['title' => 'إزالة المحدد'],
        ],
    ]"
/>
```

## Configuration Options

### Basic Configuration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `id` | string | auto-generated | Element ID |
| `name` | string | '' | Input name |
| `placeholder` | string | '' | Placeholder text |
| `class` | string | '' | CSS classes |
| `disabled` | boolean | false | Disable the select |
| `required` | boolean | false | Mark as required |

### Selection Options

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `options` | array | [] | Available options |
| `value` | mixed | null | Selected value(s) |
| `multiple` | boolean | false | Allow multiple selections |
| `maxItems` | int/null | null | Maximum selectable items |
| `allowEmptyOption` | boolean | true | Allow empty selection |

### Tom Select Specific

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `create` | boolean | false | Allow creating new options |
| `search` | boolean | true | Enable search functionality |
| `plugins` | array | [] | Tom Select plugins |
| `dir` | string | 'rtl' | Text direction |
| `maxOptions` | int | 1000 | Maximum options to display |
| `loadThrottle` | int | 300 | Search throttle delay |

### Livewire Integration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `wireModel` | string | null | Livewire model property |
| `livewireComponent` | string | null | Specific Livewire component |

### Modal Integration

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `modalOpenEvent` | string | null | Modal open event name |
| `modalCloseEvent` | string | null | Modal close event name |
| `syncOnModalOpen` | boolean | false | Sync value when modal opens |
| `clearOnModalClose` | boolean | false | Clear selection when modal closes |

### Event Handlers

| Property | Type | Description |
|----------|------|-------------|
| `onChange` | string | JavaScript code for change event |
| `onInitialize` | string | JavaScript code for initialization |
| `onItemAdd` | string | JavaScript code for item add |
| `onItemRemove` | string | JavaScript code for item remove |
| `onDropdownOpen` | string | JavaScript code for dropdown open |
| `onDropdownClose` | string | JavaScript code for dropdown close |

## Advanced Usage Examples

### Alpine.js Integration with Dynamic Templates

```blade
<div x-data="{ 
    type: 'single',
    initTomSelect() {
        this.$nextTick(() => {
            if (window.tomSelectManager) {
                window.tomSelectManager.initializeAll();
            }
        });
    }
}" x-init="$watch('type', value => initTomSelect())">
    
    <template x-if="type === 'single'">
        <div x-init="initTomSelect()">
            <x-tom-select
                name="employee"
                :options="$employees"
                wireModel="selectedEmployee"
                placeholder="اختر موظف"
                :search="true"
            />
        </div>
    </template>
    
    <template x-if="type === 'multiple'">
        <div x-init="initTomSelect()">
            <x-tom-select
                name="employees"
                :options="$employees"
                wireModel="selectedEmployees"
                :multiple="true"
                placeholder="اختر الموظفين"
                :tomOptions="[
                    'plugins' => [
                        'remove_button' => ['title' => 'إزالة المحدد'],
                    ],
                ]"
            />
        </div>
    </template>
</div>
```

### With Custom Event Handlers

```blade
<x-tom-select
    name="status"
    :options="$statuses"
    wireModel="selectedStatus"
    onChange="console.log('Status changed:', value)"
    onItemAdd="this.dispatchEvent(new CustomEvent('statusAdded', {detail: value}))"
/>
```

### With Modal Integration

```blade
<x-tom-select
    name="employee"
    :options="$employees"
    wireModel="selectedEmployee"
    modalOpenEvent="editEmployeeModal"
    modalCloseEvent="closeEmployeeModal"
    :syncOnModalOpen="true"
    :clearOnModalClose="false"
/>
```

### With Custom Plugins and Configuration

```blade
<x-tom-select
    name="category"
    :options="$categories"
    wireModel="selectedCategory"
    :tomOptions="[
        'plugins' => [
            'dropdown_input' => ['class' => 'font-hold fw-bold font-14'],
            'dropdown_header' => ['title' => 'البحث في الفئات'],
            'remove_button' => ['title' => 'إزالة المحدد'],
            'clear_button' => ['title' => 'مسح الكل'],
        ],
        'sortField' => 'text',
        'maxItems' => 3,
        'hideSelected' => true,
        'closeAfterSelect' => false,
        'selectOnTab' => true,
        'openOnFocus' => true,
    ]"
/>
```

## Plugin System

### Available Plugins

| Plugin | Description | Configuration |
|--------|-------------|---------------|
| `dropdown_input` | Adds search input in dropdown | `['class' => 'css-classes']` |
| `dropdown_header` | Adds header text to dropdown | `['title' => 'Header Text']` |
| `remove_button` | Adds remove button to selected items | `['title' => 'Remove tooltip']` |
| `clear_button` | Adds clear all button | `['title' => 'Clear all tooltip']` |

### Plugin Configuration Examples

```blade
{{-- Search input with custom styling --}}
:tomOptions="[
    'plugins' => [
        'dropdown_input' => ['class' => 'font-hold fw-bold font-14'],
    ],
]"

{{-- Header with Arabic text --}}
:tomOptions="[
    'plugins' => [
        'dropdown_header' => ['title' => 'اختر من القائمة'],
    ],
]"

{{-- Multiple plugins combined --}}
:tomOptions="[
    'plugins' => [
        'dropdown_input' => ['class' => 'font-hold'],
        'remove_button' => ['title' => 'إزالة المحدد'],
        'clear_button' => ['title' => 'مسح الكل'],
    ],
]"
```

## Option Formats

The component supports multiple option formats:

### Array Format

```php
$options = [
    ['value' => 1, 'text' => 'Option 1'],
    ['value' => 2, 'text' => 'Option 2', 'disabled' => true],
    ['value' => 3, 'text' => 'Option 3']
];
```

### Collection Format

```php
$employees = $employees->map(function ($employee) {
    return [
        'value' => $employee->id,
        'text' => $employee->name,
        'disabled' => !$employee->is_active
    ];
});
```

### Alternative Keys

The component also supports alternative key names:

```php
$options = [
    ['id' => 1, 'name' => 'Option 1'],
    ['value' => 2, 'label' => 'Option 2']
];
```

## JavaScript API

### Access Tom Select Instance

```javascript
// Get instance by element ID
const tomSelect = window.getTomSelectInstance('employee-single-select');

// Use the instance
tomSelect.setValue('new-value');
tomSelect.clear();
tomSelect.addOption({value: 'new', text: 'New Option'});
```

### Destroy Instance

```javascript
window.destroyTomSelect('employee-single-select');
```

### Manager Methods

```javascript
// Access the global manager
const manager = window.tomSelectManager;

// Reinitialize all instances
manager.reinitializeAll();

// Initialize specific element
manager.initializeElement(document.getElementById('my-select'));

// Initialize all uninitialized elements
manager.initializeAll();
```

### Manual Initialization for Alpine.js

```javascript
// In Alpine.js component
initTomSelect() {
    this.$nextTick(() => {
        if (window.tomSelectManager) {
            window.tomSelectManager.initializeAll();
        }
    });
}
```

## Livewire Integration

### Automatic Synchronization

The component automatically synchronizes with Livewire models:

```php
// In your Livewire component
public $selectedEmployee = null;

public function updatedSelectedEmployee($value)
{
    // This will be called when the selection changes
    $this->loadEmployeeDetails($value);
}
```

### Form Array Integration

```php
// Working with form arrays
public $form = [
    'employee_id' => null,
    'employee_ids' => [],
    'department_id' => null,
];

// In Blade template
:value="$form['employee_id'] ?? null"
:value="$form['employee_ids'] ?? []"
```

### Manual Synchronization

```php
// In your Livewire component
public function setEmployee($employeeId)
{
    $this->selectedEmployee = $employeeId;
    // The Tom Select component will automatically update
}
```

### Event Listening

```php
// In your Livewire component
protected $listeners = ['employeeChanged' => 'handleEmployeeChange'];

public function handleEmployeeChange($employeeId)
{
    $this->selectedEmployee = $employeeId;
}
```

## Styling and RTL Support

### Custom CSS Classes

```blade
<x-tom-select
    name="status"
    :options="$statuses"
    wireModel="selectedStatus"
    class="form-control-lg border-primary font-hold"
/>
```

### RTL Support

The component automatically handles RTL layout:

```blade
<x-tom-select
    name="category"
    :options="$categories"
    wireModel="selectedCategory"
    dir="rtl"
    class="font-hold"
/>
```

## Performance Considerations

1. **Initialization**: Components are initialized only once and reused
2. **Cleanup**: Automatic cleanup of destroyed instances
3. **Throttling**: Search queries are throttled to prevent excessive requests
4. **Lazy Loading**: Options are loaded only when needed
5. **DOM Mutation Observer**: Automatically detects new elements without manual intervention
6. **Memory Management**: Proper cleanup of event listeners and instances

## Error Handling

The component includes comprehensive error handling:

- Invalid configurations are logged with warnings
- Failed initializations are caught and logged
- Livewire sync errors are handled gracefully
- Missing dependencies are detected and reported
- Auto-recovery for initialization failures
- Debug logging for troubleshooting

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Internet Explorer 11+ (with polyfills)
- Mobile browsers (iOS Safari, Chrome Mobile)
- RTL layout support across all browsers

## Dependencies

- **Tom Select**: JavaScript library for enhanced select dropdowns (v2.3.1+)
- **Livewire**: Laravel Livewire for reactive components (v3+)
- **Alpine.js**: For dynamic template integration (v3+)
- **Laravel**: Laravel framework (v11+)

## Troubleshooting

### Common Issues

1. **Tom Select not initializing in Alpine.js templates**: 
   - Ensure you're using `x-init="initTomSelect()"` on template divs
   - Use `$nextTick()` for proper timing

2. **"Untitled" button appearing**: 
   - Remove `dropdown_input` plugin if you have `:search="true"`
   - Use `dropdown_header` instead for custom headers

3. **Livewire sync issues**: 
   - Check wire:model and component structure
   - Use array notation for form arrays: `$form['field'] ?? null`

4. **Auto-selection on page load**: 
   - The component prevents this automatically
   - Ensure `:value` prop is properly set to null/empty

5. **Modal integration problems**: 
   - Verify event names and listeners
   - Use proper Livewire event dispatching

6. **Performance issues**: 
   - Check for excessive re-initialization
   - Use DOM mutation observer (built-in)

### Debug Mode

Enable debug logging:

```javascript
// In browser console
window.tomSelectManager.debug = true;

// Check if Tom Select is loaded
console.log(typeof TomSelect);

// Check manager status
console.log(window.tomSelectManager);
```

### Console Logs

The component provides detailed console logging:

- Initialization success/failure
- Configuration parsing
- Event handling
- Error messages
- Auto-selection clearing
- Livewire synchronization

### Alpine.js Debugging

```javascript
// Check Alpine.js integration
console.log(typeof Alpine);

// Watch for DOM mutations
const observer = new MutationObserver(console.log);
observer.observe(document.body, { childList: true, subtree: true });
```

## Migration from Previous Versions

If migrating from older versions:

1. **Update prop names**: Some properties have changed
2. **Replace `tomOptions`**: Use specific props where possible
3. **Update event handler syntax**: New function-based approach
4. **Test Alpine.js integration**: Add proper initialization calls
5. **Verify Livewire synchronization**: Check array notation usage
6. **Update plugin configurations**: New plugin syntax

## Real-World Examples

### Employee Management Form

```blade
{{-- Attendance Processing Form --}}
<div x-data="{ 
    type: $wire.form.processing_type,
    initTomSelect() {
        this.$nextTick(() => {
            if (window.tomSelectManager) {
                window.tomSelectManager.initializeAll();
            }
        });
    }
}" x-init="$watch('type', value => {
    $wire.set('form.processing_type', value);
    initTomSelect();
})">

    {{-- Single Employee Selection --}}
    <template x-if="$wire.form.processing_type === 'single'">
        <div x-init="initTomSelect()">
            <label class="form-label font-hold">{{ __('اختر الموظف') }}</label>
            <x-tom-select
                id="employee-single-select"
                name="employee_id"
                :options="collect($employees)->map(fn($employee) => ['value' => $employee->id, 'text' => $employee->name])->toArray()"
                wireModel="form.employee_id"
                placeholder="{{ __('اختر الموظف') }}"
                class="form-select font-hold"
                :allowEmptyOption="true"
                :search="true"
                :value="$form['employee_id'] ?? null"
                :tomOptions="[
                    'plugins' => [
                        'dropdown_input' => ['class' => 'font-hold fw-bold font-14'],
                        'remove_button' => ['title' => 'إزالة المحدد'],
                    ],
                ]"
            />
        </div>
    </template>

    {{-- Multiple Employee Selection --}}
    <template x-if="$wire.form.processing_type === 'multiple'">
        <div x-init="initTomSelect()">
            <label class="form-label font-hold">{{ __('اختر الموظفين') }}</label>
            <x-tom-select
                id="employee-multi-select"
                name="employee_ids"
                :options="collect($employees)->map(fn($employee) => ['value' => $employee->id, 'text' => $employee->name])->toArray()"
                wireModel="form.employee_ids"
                placeholder="{{ __('اختر الموظفين') }}"
                class="form-select font-hold"
                :multiple="true"
                :allowEmptyOption="false"
                :search="true"
                :value="$form['employee_ids'] ?? []"
                :tomOptions="[
                    'plugins' => [
                        'dropdown_input' => ['class' => 'font-hold fw-bold font-14'],
                        'remove_button' => ['title' => 'إزالة المحدد'],
                    ],
                ]"
            />
        </div>
    </template>
</div>
```

### Product Category Selection

```blade
{{-- Product Management --}}
<div>
    <x-tom-select
        name="category"
        :options="$categories"
        wireModel="filters.category"
        placeholder="اختر الفئة"
        :search="true"
        :tomOptions="[
            'plugins' => [
                'dropdown_header' => ['title' => 'فئات المنتجات'],
                'clear_button' => ['title' => 'مسح الاختيار'],
            ],
        ]"
    />
    
    <x-tom-select
        name="tags"
        :options="$tags"
        wireModel="filters.tags"
        placeholder="اختر العلامات"
        :multiple="true"
        :maxItems="5"
        :tomOptions="[
            'plugins' => [
                'remove_button' => ['title' => 'إزالة العلامة'],
            ],
        ]"
    />
</div>
```

### Modal Integration Example

```blade
{{-- Employee Selection Modal --}}
<x-tom-select
    name="employee"
    :options="$employees"
    wireModel="selectedEmployee"
    modalOpenEvent="openEmployeeModal"
    modalCloseEvent="closeEmployeeModal"
    :syncOnModalOpen="true"
    :clearOnModalClose="false"
    placeholder="اختر موظف"
    :tomOptions="[
        'plugins' => [
            'dropdown_input' => ['class' => 'font-hold'],
        ],
    ]"
/>
```

## Best Practices

1. **Always use proper Arabic placeholders and labels**
2. **Include `font-hold` class for consistency**
3. **Use array notation for Livewire form arrays**
4. **Add `x-init="initTomSelect()"` for Alpine.js templates**
5. **Configure plugins based on your use case**
6. **Test across different browsers and devices**
7. **Use proper RTL styling and layout**
8. **Handle empty states gracefully**
9. **Provide meaningful error messages in Arabic**
10. **Use semantic option formats for better accessibility**

## Future Enhancements

Planned features for future versions:

- **AJAX option loading** for large datasets
- **Virtual scrolling** for performance
- **Custom rendering templates**
- **Advanced validation integration**
- **Accessibility improvements**
- **Mobile-optimized interface**
- **Theme customization system**

This documentation provides comprehensive guidance for using the optimized Tom Select component in your Laravel Livewire applications with full Alpine.js integration and Arabic RTL support. 