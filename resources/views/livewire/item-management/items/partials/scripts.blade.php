<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('livewire:init', () => {
            window.addEventListener('open-modal', event => {
                let modal = new bootstrap.Modal(document.getElementById(event.detail[0]));
                modal.show();
                // Keep page scrollbar visible even when modal is open
                document.documentElement.style.overflowY = 'scroll';
                document.body.style.overflowY = 'auto';
            });

            window.addEventListener('close-modal', event => {
                let modal = bootstrap.Modal.getInstance(document.getElementById(event.detail[
                    0]));
                if (modal) {
                    modal.hide();
                }
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                // Always restore scrolling
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.documentElement.style.overflowY = 'scroll';
                document.body.style.overflowY = 'auto';
            });
            // Auto-focus functionality
            Livewire.on('auto-focus', function (inputId) {
                // Add a small delay to ensure DOM is updated
                setTimeout(() => {
                    const element = document.getElementById(inputId);
                    if (element) {
                        element.focus();
                    }
                }, 100);
            });

            // منع زر الإدخال (Enter) من حفظ النموذج
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('keydown', function (e) {
                    // إذا كان الزر Enter وتم التركيز على input وليس textarea أو زر
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target
                        .type !== 'submit' && e.target.type !== 'button') {
                        e.preventDefault();
                    }
                });
            });

            // إغلاق المودال عند الضغط على Escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('closeModal');
                }
            });

            // إغلاق المودال عند النقر خارج المودال
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    Livewire.dispatch('closeModal');
                }
            });

            // حفظ البيانات عند الضغط على Enter في المودال
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && document.querySelector('.modal.show')) {
                    const modalInput = document.querySelector('.modal.show input[type="text"]');
                    if (modalInput && modalInput === document.activeElement) {
                        e.preventDefault();
                        Livewire.dispatch('saveModalData');
                    }
                }
            });
        });


        // Safety net: when any Bootstrap modal hides, clean body/backdrops
        document.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.documentElement.style.overflowY = 'scroll';
            document.body.style.overflowY = 'auto';
        });

        document.addEventListener('shown.bs.modal', function () {
            // Ensure body/html keep scrollbar visible while modal shown
            document.documentElement.style.overflowY = 'scroll';
            document.body.style.overflowY = 'auto';
        });
    });

    // ========== CLIENT-SIDE CALCULATIONS ==========
    
    /**
     * Update units cost and prices based on conversion factor
     * This replaces updateUnitsCostAndPrices() Livewire function
     * Uses Livewire's wire:model to update values directly
     */
    window.updateUnitsCostAndPrices = function(index) {
        if (typeof Livewire === 'undefined') return;
        
        const uValInput = document.querySelector(`input[wire\\:model="unitRows.${index}.u_val"]`);
        if (!uValInput) return;
        
        const uVal = parseFloat(uValInput.value) || 0;
        
        if (index === 0 || uVal <= 0) return;
        
        // Get base unit values
        const baseCostInput = document.querySelector('input[wire\\:model="unitRows.0.cost"]');
        const baseCost = parseFloat(baseCostInput?.value) || 0;
        
        // Calculate and update cost
        const costInput = document.querySelector(`input[wire\\:model="unitRows.${index}.cost"]`);
        if (costInput) {
            const calculatedCost = uVal * baseCost;
            costInput.value = calculatedCost;
            // Trigger Livewire update
            costInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Calculate and update prices
        document.querySelectorAll('input[wire\\:model^="unitRows.0.prices."]').forEach(basePriceInput => {
            const match = basePriceInput.getAttribute('wire:model').match(/prices\.(\d+)/);
            if (match) {
                const priceId = match[1];
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const calculatedPrice = uVal * basePrice;
                
                const priceInput = document.querySelector(`input[wire\\:model="unitRows.${index}.prices.${priceId}"]`);
                if (priceInput) {
                    priceInput.value = calculatedPrice;
                    priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        });
    };
    
    /**
     * Update units cost when base cost changes
     * This replaces updateUnitsCost() Livewire function
     */
    window.updateUnitsCost = function(index) {
        if (index !== 0) return;
        
        const baseCostInput = document.querySelector('input[wire\\:model="unitRows.0.cost"]');
        if (!baseCostInput) return;
        
        const baseCost = parseFloat(baseCostInput.value) || 0;
        
        // Update all other units' costs
        document.querySelectorAll('input[wire\\:model^="unitRows."][wire\\:model$=".cost"]').forEach(input => {
            const match = input.getAttribute('wire:model').match(/unitRows\.(\d+)\.cost/);
            if (match) {
                const unitIndex = parseInt(match[1]);
                if (unitIndex !== 0) {
                    const uValInput = document.querySelector(`input[wire\\:model="unitRows.${unitIndex}.u_val"]`);
                    if (uValInput) {
                        const uVal = parseFloat(uValInput.value) || 0;
                        const calculatedCost = uVal * baseCost;
                        input.value = calculatedCost;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        });
    };
    
    /**
     * Update combination units cost and prices
     * This replaces updateCombinationUnitsCostAndPrices() Livewire function
     */
    window.updateCombinationUnitsCostAndPrices = function(combinationKey, index) {
        const uValInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.${index}.u_val"]`);
        const costInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.${index}.cost"]`);
        
        if (!uValInput || !costInput) return;
        
        const uVal = parseFloat(uValInput.value) || 0;
        const baseCost = parseFloat(document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.0.cost"]`)?.value) || 0;
        const basePrices = {};
        
        // Get all price inputs for base unit (index 0) of this combination
        document.querySelectorAll(`input[wire\\:model^="combinationUnitRows.${combinationKey}.0.prices."]`).forEach(input => {
            const match = input.getAttribute('wire:model').match(/prices\.(\d+)/);
            if (match) {
                basePrices[match[1]] = parseFloat(input.value) || 0;
            }
        });
        
        if (index !== 0 && uVal > 0) {
            // Calculate cost for this unit
            const calculatedCost = uVal * baseCost;
            costInput.value = calculatedCost;
            costInput.dispatchEvent(new Event('input', { bubbles: true }));
            
            // Calculate prices for this unit
            Object.keys(basePrices).forEach(priceId => {
                const priceInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.${index}.prices.${priceId}"]`);
                if (priceInput) {
                    const calculatedPrice = uVal * basePrices[priceId];
                    priceInput.value = calculatedPrice;
                    priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    };
    
    /**
     * Update combination units cost when base cost changes
     * This replaces updateCombinationUnitsCost() Livewire function
     */
    window.updateCombinationUnitsCost = function(combinationKey, index) {
        if (index !== 0) return;
        
        const baseCostInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.0.cost"]`);
        if (!baseCostInput) return;
        
        const baseCost = parseFloat(baseCostInput.value) || 0;
        
        // Update all other units' costs in this combination
        document.querySelectorAll(`input[wire\\:model^="combinationUnitRows.${combinationKey}."][wire\\:model$=".cost"]`).forEach(input => {
            const match = input.getAttribute('wire:model').match(/combinationUnitRows\.\w+\.(\d+)\.cost/);
            if (match) {
                const unitIndex = parseInt(match[1]);
                if (unitIndex !== 0) {
                    const uValInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.${unitIndex}.u_val"]`);
                    if (uValInput) {
                        const uVal = parseFloat(uValInput.value) || 0;
                        const calculatedCost = uVal * baseCost;
                        input.value = calculatedCost;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        });
    };
    
    /**
     * Update combination prices when base price changes
     * This replaces updateCombinationPrices() Livewire function
     */
    window.updateCombinationPrices = function(combinationKey, index, priceId) {
        if (index !== 0) return;
        
        const basePriceInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.0.prices.${priceId}"]`);
        if (!basePriceInput) return;
        
        const basePrice = parseFloat(basePriceInput.value) || 0;
        
        // Update all other units' prices in this combination
        document.querySelectorAll(`input[wire\\:model^="combinationUnitRows.${combinationKey}."][wire\\:model$=".prices.${priceId}"]`).forEach(input => {
            const match = input.getAttribute('wire:model').match(/combinationUnitRows\.\w+\.(\d+)\.prices/);
            if (match) {
                const unitIndex = parseInt(match[1]);
                if (unitIndex !== 0) {
                    const uValInput = document.querySelector(`input[wire\\:model="combinationUnitRows.${combinationKey}.${unitIndex}.u_val"]`);
                    if (uValInput) {
                        const uVal = parseFloat(uValInput.value) || 0;
                        const calculatedPrice = uVal * basePrice;
                        input.value = calculatedPrice;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
        });
    };
</script>