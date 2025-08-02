@props([
    // Basic configuration
    'id' => 'tom-select-' . uniqid(),
    'name' => '',
    'placeholder' => '',
    'class' => '',
    'disabled' => false,
    'required' => false,
    
    // Selection options
    'options' => [],
    'value' => null,
    'multiple' => false,
    'maxItems' => null,
    'allowEmptyOption' => false,
    
    // Livewire integration
    'wireModel' => null,
    'livewireComponent' => null,
    
    // Tom Select specific options
    'create' => false,
    'search' => true,
    'plugins' => [],
    'dir' => 'rtl',
    'maxOptions' => 1000,
    'loadThrottle' => 300,
    
    // Event handlers
    'onChange' => null,
    'onInitialize' => null,
    'onItemAdd' => null,
    'onItemRemove' => null,
    'onDropdownOpen' => null,
    'onDropdownClose' => null,
    
    // Modal integration
    'modalOpenEvent' => null,
    'modalCloseEvent' => null,
    'syncOnModalOpen' => false,
    'clearOnModalClose' => false,
    
    // Custom rendering
    'customRender' => [],
    
    // Advanced options
    'tomOptions' => [],
])

@php
    // Determine if multiple selection is enabled
    $isMultiple = $multiple || ($maxItems !== null && $maxItems > 1);
    
    // Set default maxItems based on multiple setting
    if ($maxItems === null) {
        $maxItems = $isMultiple ? null : 1;
    }
    
    // Prepare default plugins
    $defaultPlugins = [];
    if ($isMultiple) {
        $defaultPlugins[] = 'remove_button';
    }
    if ($search) {
        // $defaultPlugins[] = 'dropdown_header';
        $defaultPlugins[] = 'remove_button';
    }
    
    // Merge plugins
    $allPlugins = array_unique(array_merge($defaultPlugins, $plugins));
    
    // Build Tom Select configuration
    $config = array_merge([
        'create' => $create,
        'maxItems' => $maxItems,
        'maxOptions' => $maxOptions,
        'allowEmptyOption' => $allowEmptyOption,
        'placeholder' => $placeholder,
        'plugins' => $allPlugins,
        'dir' => $dir,
        'loadThrottle' => $loadThrottle,
        'searchField' => ['text', 'value'],
        'valueField' => 'value',
        'labelField' => 'text',
        'sortField' => 'text',
        'preload' => false,
        'hideSelected' => $isMultiple,
        'closeAfterSelect' => !$isMultiple,
        'selectOnTab' => true,
        'openOnFocus' => true,
    ], $tomOptions);
    
    // Add custom render functions if provided
    if (!empty($customRender)) {
        $config['render'] = $customRender;
    }
    
    // Prepare data attributes for JavaScript
    $dataAttributes = [
        'data-tom-select' => true,
        'data-tom-config' => json_encode($config),
        'data-wire-model' => $wireModel,
        'data-livewire-component' => $livewireComponent,
        'data-modal-open-event' => $modalOpenEvent,
        'data-modal-close-event' => $modalCloseEvent,
        'data-sync-on-modal-open' => $syncOnModalOpen ? 'true' : 'false',
        'data-clear-on-modal-close' => $clearOnModalClose ? 'true' : 'false',
        'data-has-initial-value' => ($value !== null && $value !== '') ? 'true' : 'false',
    ];
    
    // Add event handlers as data attributes
    $eventHandlers = [
        'onChange' => $onChange,
        'onInitialize' => $onInitialize,
        'onItemAdd' => $onItemAdd,
        'onItemRemove' => $onItemRemove,
        'onDropdownOpen' => $onDropdownOpen,
        'onDropdownClose' => $onDropdownClose,
    ];
    
    foreach ($eventHandlers as $event => $handler) {
        if ($handler) {
            $dataAttributes["data-{$event}"] = $handler;
        }
    }
    
    // Generate unique identifier for this component
    $componentId = 'tom-select-' . substr(md5($id . $name), 0, 8);
@endphp

<div class="tom-select-wrapper" wire:ignore>
    <select
        id="{{ $id }}"
        name="{{ $name }}{{ $isMultiple ? '[]' : '' }}"
        class="tom-select {{ $class }}"
        autocomplete="off"
        @if($wireModel) wire:model.live.debounce.200ms="{{ $wireModel }}" @endif
        @if($isMultiple) multiple @endif
        @if($disabled) disabled @endif
        @if($required) required @endif
        @foreach($dataAttributes as $attr => $val)
            @if($val !== null && $val !== false)
                {{ $attr }}="{{ $val }}"
            @endif
        @endforeach
    >
        @if($allowEmptyOption && !$required)
            <option value="">{{ $placeholder ?: __('اختر خيار') }}</option>
        @endif
        
        @foreach($options as $option)
            @php
                $optionValue = is_array($option) ? ($option['value'] ?? $option['id'] ?? '') : $option;
                $optionText = is_array($option) ? ($option['text'] ?? $option['label'] ?? $option['name'] ?? $optionValue) : $option;
                $isSelected = false;
                
                // Only select if we have a valid value to match against
                if ($value !== null && $value !== '') {
                    if ($isMultiple && is_array($value)) {
                        $isSelected = in_array($optionValue, $value);
                    } elseif (!$isMultiple) {
                        $isSelected = (string)$value === (string)$optionValue;
                    }
                }
            @endphp
            
            <option 
                value="{{ $optionValue }}" 
                @if($isSelected) selected @endif
                @if(is_array($option) && isset($option['disabled']) && $option['disabled']) disabled @endif
            >
                {{ $optionText }}
            </option>
        @endforeach
    </select>
</div>

@once
    @push('scripts')
    <script>
        /**
         * Tom Select Component Manager
         * Handles initialization, updates, and cleanup of Tom Select instances
         */
        class TomSelectManager {
            constructor() {
                this.instances = new Map();
                this.initialized = false;
                this.initPromise = null;
            }
            
            /**
             * Initialize all Tom Select instances
             */
            async init() {
                if (this.initialized) return;
                if (this.initPromise) return this.initPromise;
                
                this.initPromise = new Promise((resolve) => {
                    if (typeof TomSelect === 'undefined') {
                        console.warn('TomSelect is not loaded. Please include Tom Select library.');
                        resolve();
                        return;
                    }
                    
                    this.initializeAll();
                    this.initialized = true;
                    resolve();
                });
                
                return this.initPromise;
            }
            
            /**
             * Initialize all Tom Select elements on the page
             */
            initializeAll() {
                // First, clean up orphaned instances
                this.cleanupOrphanedInstances();
                
                const elements = document.querySelectorAll('select[data-tom-select]:not([data-tom-initialized])');
                elements.forEach(element => this.initializeElement(element));
            }
            
            /**
             * Clean up instances for elements that no longer exist in the DOM
             */
            cleanupOrphanedInstances() {
                this.instances.forEach((instance, elementId) => {
                    const element = document.getElementById(elementId);
                    if (!element) {
                        try {
                            instance.destroy();
                            this.instances.delete(elementId);
                            console.log(`Cleaned up orphaned Tom Select instance: ${elementId}`);
                        } catch (error) {
                            console.warn('Error cleaning up orphaned Tom Select instance:', error);
                        }
                    }
                });
            }
            
            /**
             * Initialize a single Tom Select element
             */
            initializeElement(element) {
                try {
                    const elementId = element.id;
                    
                    // Prevent double initialization unless forced
                    if (element.hasAttribute('data-tom-initialized')) {
                        // Check if this is a reinitialization scenario (conditional rendering)
                        const existingInstance = this.instances.get(elementId);
                        if (existingInstance) {
                            try {
                                existingInstance.destroy();
                                this.instances.delete(elementId);
                            } catch (error) {
                                console.warn('Error destroying existing Tom Select instance:', error);
                            }
                        }
                        element.removeAttribute('data-tom-initialized');
                    }
                    
                    // Parse configuration
                    const config = this.parseConfig(element);
                    
                    // Create Tom Select instance
                    const tomSelect = new TomSelect(element, config);
                    
                    // Check if we should clear the selection based on data attribute
                    const hasInitialValue = element.getAttribute('data-has-initial-value') === 'true';
                    const wireModel = element.getAttribute('data-wire-model');
                    
                    // If no initial value is set, ensure Tom Select starts empty
                    if (!hasInitialValue) {
                        // Immediate clear
                        tomSelect.clear(true);
                        
                        // Additional aggressive clear using setTimeout to catch any auto-selection
                        setTimeout(() => {
                            const currentValue = tomSelect.getValue();
                            if (currentValue && currentValue !== '') {
                                console.log('Force clearing Tom Select auto-selection:', currentValue);
                                tomSelect.clear(true);
                                
                                // If there's a Livewire model, also clear that
                                if (wireModel && typeof Livewire !== 'undefined') {
                                    const livewireElement = element.closest('[wire\\:id]');
                                    if (livewireElement) {
                                        const componentId = livewireElement.getAttribute('wire:id');
                                        const component = Livewire.find(componentId);
                                        if (component) {
                                            component.set(wireModel, '');
                                        }
                                    }
                                }
                            }
                        }, 50);
                        
                        // Additional timeout for more persistent cases
                        setTimeout(() => {
                            const currentValue = tomSelect.getValue();
                            if (currentValue && currentValue !== '') {
                                console.log('Second force clearing Tom Select auto-selection:', currentValue);
                                tomSelect.clear(true);
                            }
                        }, 200);
                    }
                    
                    // Store instance
                    this.instances.set(elementId, tomSelect);
                    
                    // Set up event listeners
                    this.setupEventListeners(element, tomSelect);
                    
                    // Set up Livewire integration
                    this.setupLivewireIntegration(element, tomSelect);
                    
                    // Set up modal integration
                    this.setupModalIntegration(element, tomSelect);
                    
                    // Mark as initialized
                    element.setAttribute('data-tom-initialized', 'true');
                    
                    console.log(`Tom Select initialized for element: ${elementId}`);
                    
                } catch (error) {
                    console.error('Error initializing Tom Select:', error, element);
                }
            }
            
            /**
             * Parse configuration from element data attributes
             */
            parseConfig(element) {
                let config = {};
                
                try {
                    const configData = element.getAttribute('data-tom-config');
                    if (configData) {
                        config = JSON.parse(configData);
                    }
                } catch (error) {
                    console.warn('Error parsing Tom Select config:', error);
                }
                
                return config;
            }
            
            /**
             * Set up custom event listeners
             */
            setupEventListeners(element, tomSelect) {
                const eventHandlers = [
                    'onChange', 'onInitialize', 'onItemAdd', 'onItemRemove',
                    'onDropdownOpen', 'onDropdownClose'
                ];
                
                eventHandlers.forEach(eventName => {
                    const handlerCode = element.getAttribute(`data-${eventName}`);
                    if (handlerCode) {
                        try {
                            const handler = new Function('value', 'instance', handlerCode);
                            const eventType = eventName.replace('on', '').toLowerCase();
                            tomSelect.on(eventType, (value) => handler(value, tomSelect));
                        } catch (error) {
                            console.warn(`Error setting up ${eventName} handler:`, error);
                        }
                    }
                });
            }
            
            /**
             * Set up Livewire integration
             */
            setupLivewireIntegration(element, tomSelect) {
                const wireModel = element.getAttribute('data-wire-model');
                
                if (!wireModel || typeof Livewire === 'undefined') return;
                
                // Get Livewire component
                const livewireElement = element.closest('[wire\\:id]');
                if (!livewireElement) return;
                
                const componentId = livewireElement.getAttribute('wire:id');
                const component = Livewire.find(componentId);
                
                if (!component) return;
                
                // Sync Tom Select changes to Livewire
                tomSelect.on('change', (value) => {
                    try {
                        component.set(wireModel, value);
                    } catch (error) {
                        console.warn('Error syncing Tom Select to Livewire:', error);
                    }
                });
                
                // Listen for Livewire updates
                component.$watch(wireModel, (value) => {
                    try {
                        if (JSON.stringify(tomSelect.getValue()) !== JSON.stringify(value)) {
                            tomSelect.setValue(value, true);
                        }
                    } catch (error) {
                        console.warn('Error syncing Livewire to Tom Select:', error);
                    }
                });
            }
            
            /**
             * Set up modal integration
             */
            setupModalIntegration(element, tomSelect) {
                const modalOpenEvent = element.getAttribute('data-modal-open-event');
                const modalCloseEvent = element.getAttribute('data-modal-close-event');
                const syncOnModalOpen = element.getAttribute('data-sync-on-modal-open') === 'true';
                const clearOnModalClose = element.getAttribute('data-clear-on-modal-close') === 'true';
                
                if (modalOpenEvent && typeof Livewire !== 'undefined') {
                    Livewire.on(modalOpenEvent, () => {
                        if (syncOnModalOpen) {
                            const wireModel = element.getAttribute('data-wire-model');
                            if (wireModel) {
                                const livewireElement = element.closest('[wire\\:id]');
                                const componentId = livewireElement?.getAttribute('wire:id');
                                const component = componentId ? Livewire.find(componentId) : null;
                                
                                if (component) {
                                    const value = component.get(wireModel);
                                    if (value) tomSelect.setValue(value, true);
                                }
                            }
                        }
                    });
                }
                
                if (modalCloseEvent && typeof Livewire !== 'undefined') {
                    Livewire.on(modalCloseEvent, () => {
                        if (clearOnModalClose) {
                            tomSelect.clear(true);
                        }
                    });
                }
            }
            
            /**
             * Destroy a Tom Select instance
             */
            destroy(elementId) {
                const instance = this.instances.get(elementId);
                if (instance) {
                    instance.destroy();
                    this.instances.delete(elementId);
                }
            }
            
            /**
             * Reinitialize all instances (useful for Livewire updates)
             */
            reinitializeAll() {
                // Clean up existing instances
                this.instances.forEach((instance, elementId) => {
                    const element = document.getElementById(elementId);
                    if (!element) {
                        instance.destroy();
                        this.instances.delete(elementId);
                    } else {
                        // If element exists but might need reinitialization, destroy and reinitialize
                        try {
                            instance.destroy();
                            this.instances.delete(elementId);
                            // Remove the initialized attribute to allow reinitialization
                            element.removeAttribute('data-tom-initialized');
                        } catch (error) {
                            console.warn('Error destroying Tom Select instance:', error);
                        }
                    }
                });
                
                // Initialize new elements
                this.initializeAll();
            }
            
            /**
             * Force reinitialize specific elements by selector
             */
            reinitializeBySelector(selector) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    const elementId = element.id;
                    const existingInstance = this.instances.get(elementId);
                    
                    if (existingInstance) {
                        try {
                            existingInstance.destroy();
                            this.instances.delete(elementId);
                        } catch (error) {
                            console.warn('Error destroying existing Tom Select instance:', error);
                        }
                    }
                    
                    element.removeAttribute('data-tom-initialized');
                    this.initializeElement(element);
                });
            }
        }
        
        // Create global instance
        window.tomSelectManager = new TomSelectManager();
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.tomSelectManager.init();
            });
        } else {
            window.tomSelectManager.init();
        }
        
        // Livewire integration
        if (typeof Livewire !== 'undefined') {
            // Reinitialize after Livewire updates
            document.addEventListener('livewire:navigated', () => {
                window.tomSelectManager.reinitializeAll();
            });
            
            document.addEventListener('livewire:updated', () => {
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 100);
            });
            
            // Handle conditional rendering better
            document.addEventListener('livewire:updated', () => {
                // Force reinitialize after a longer delay to ensure DOM is fully updated
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 300);
            });
            
            // Listen for specific model changes that might trigger conditional rendering
            document.addEventListener('livewire:model-updated', () => {
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 200);
            });
            
            // Listen for processing type changes specifically
            document.addEventListener('processing-type-changed', () => {
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 100);
            });
            
            // Listen for explicit Tom Select reinitialization
            document.addEventListener('reinitialize-tom-select', () => {
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 150);
            });
            
            // More aggressive reinitialization for processing type changes
            document.addEventListener('processing-type-changed', () => {
                // Force cleanup and reinitialization with multiple attempts
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 100);
                
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 300);
                
                setTimeout(() => {
                    window.tomSelectManager.reinitializeAll();
                }, 500);
                
                // Specifically target the selectedEmployee Tom Select
                setTimeout(() => {
                    window.tomSelectManager.reinitializeBySelector('#selectedEmployee');
                }, 200);
                
                setTimeout(() => {
                    window.tomSelectManager.reinitializeBySelector('#selectedEmployee');
                }, 400);
            });
        }
        
        // Alpine.js integration - listen for DOM mutations
        if (typeof Alpine !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1) { // Element node
                                // Check if the added node contains tom-select elements
                                const tomSelectElements = node.querySelectorAll ? 
                                    node.querySelectorAll('select[data-tom-select]:not([data-tom-initialized])') : [];
                                
                                if (tomSelectElements.length > 0) {
                                    setTimeout(() => {
                                        window.tomSelectManager.initializeAll();
                                    }, 50);
                                }
                                
                                // Check if the node itself is a tom-select element
                                if (node.hasAttribute && node.hasAttribute('data-tom-select') && !node.hasAttribute('data-tom-initialized')) {
                                    setTimeout(() => {
                                        window.tomSelectManager.initializeAll();
                                    }, 50);
                                }
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // Additional observer specifically for Livewire conditional rendering
        const livewireObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    // Check if any Tom Select elements were added or removed
                    const hasTomSelectChanges = mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0;
                    
                    if (hasTomSelectChanges) {
                        // Check for Tom Select elements in the added nodes
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === 1) { // Element node
                                const tomSelectElements = node.querySelectorAll ? 
                                    node.querySelectorAll('select[data-tom-select]') : [];
                                
                                if (tomSelectElements.length > 0) {
                                    setTimeout(() => {
                                        window.tomSelectManager.reinitializeAll();
                                    }, 100);
                                }
                            }
                        });
                    }
                }
            });
        });
        
        // Observe the entire document for Livewire changes
        livewireObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Expose utility functions
        window.getTomSelectInstance = function(elementId) {
            return window.tomSelectManager.instances.get(elementId);
        };
        
        window.destroyTomSelect = function(elementId) {
            window.tomSelectManager.destroy(elementId);
        };
        
        window.reinitializeTomSelect = function(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                // Remove initialization flag to force reinitialization
                element.removeAttribute('data-tom-initialized');
                // Destroy existing instance if any
                const existingInstance = window.tomSelectManager.instances.get(elementId);
                if (existingInstance) {
                    try {
                        existingInstance.destroy();
                        window.tomSelectManager.instances.delete(elementId);
                    } catch (error) {
                        console.warn('Error destroying existing Tom Select instance:', error);
                    }
                }
                // Reinitialize
                window.tomSelectManager.initializeElement(element);
            }
        };
        
        window.reinitializeAllTomSelects = function() {
            window.tomSelectManager.reinitializeAll();
        };
        
        window.reinitializeTomSelectBySelector = function(selector) {
            window.tomSelectManager.reinitializeBySelector(selector);
        };
    </script>
    @endpush
@endonce 