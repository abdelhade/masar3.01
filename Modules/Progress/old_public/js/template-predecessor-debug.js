// Template Predecessor Debug Script
// هذا الملف لتشخيص وإصلاح مشاكل الـ predecessor في الـ templates

document.addEventListener('DOMContentLoaded', function() {
    console.log('Template Predecessor Debug Script Loaded');
    
    // دالة لتشخيص حالة الـ predecessors
    function debugPredecessors() {
        console.log('=== Template Predecessor Debug ===');
        
        // فحص البيانات المرسلة من الـ server
        if (typeof templateItems !== 'undefined') {
            console.log('Template Items from Server:', templateItems);
            
            templateItems.forEach((item, index) => {
                console.log(`Item ${index + 1}:`, {
                    id: item.id,
                    work_item_id: item.work_item_id,
                    name: item.work_item?.name || 'Unknown',
                    predecessor: item.predecessor,
                    predecessor_type: typeof item.predecessor,
                    full_item: item
                });
            });
        }
        
        // فحص الـ dropdowns في الصفحة
        const predecessorSelects = document.querySelectorAll('.predecessor-select');
        console.log(`Found ${predecessorSelects.length} predecessor dropdowns`);
        
        predecessorSelects.forEach((select, index) => {
            const row = select.closest('tr');
            const itemId = row ? row.dataset.itemId : 'unknown';
            const options = Array.from(select.options).map(opt => ({
                value: opt.value,
                text: opt.textContent,
                selected: opt.selected
            }));
            
            console.log(`Dropdown ${index + 1} (Item ID: ${itemId}):`, {
                current_value: select.value,
                options: options,
                select_name: select.name
            });
        });
    }
    
    // دالة لإصلاح الـ predecessors
    function fixPredecessors() {
        console.log('Attempting to fix predecessors...');
        
        if (typeof templateItems === 'undefined') {
            console.error('templateItems not found');
            return;
        }
        
        templateItems.forEach(templateItem => {
            if (templateItem.predecessor) {
                const row = document.querySelector(`tr[data-item-id="${templateItem.work_item_id}"]`);
                if (row) {
                    const predecessorSelect = row.querySelector('.predecessor-select');
                    if (predecessorSelect) {
                        // محاولة تعيين الـ predecessor
                        const targetValue = templateItem.predecessor.toString();
                        
                        // البحث في الخيارات المتاحة
                        const option = Array.from(predecessorSelect.options).find(opt => 
                            opt.value === targetValue
                        );
                        
                        if (option) {
                            option.selected = true;
                            predecessorSelect.value = targetValue;
                            console.log('✅ Predecessor fixed:', {
                                item: templateItem.work_item_id,
                                predecessor: targetValue,
                                predecessor_name: option.textContent
                            });
                        } else {
                            console.warn('❌ Predecessor option not found:', {
                                item: templateItem.work_item_id,
                                looking_for: targetValue,
                                available_options: Array.from(predecessorSelect.options).map(opt => opt.value)
                            });
                        }
                    }
                }
            }
        });
    }
    
    // تشغيل التشخيص بعد تحميل الصفحة
    setTimeout(() => {
        debugPredecessors();
        fixPredecessors();
    }, 1000);
    
    // إضافة أزرار للتشخيص (للتطوير فقط)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        const debugButton = document.createElement('button');
        debugButton.textContent = 'Debug Predecessors';
        debugButton.className = 'btn btn-info btn-sm';
        debugButton.style.position = 'fixed';
        debugButton.style.top = '10px';
        debugButton.style.right = '10px';
        debugButton.style.zIndex = '9999';
        debugButton.onclick = debugPredecessors;
        
        const fixButton = document.createElement('button');
        fixButton.textContent = 'Fix Predecessors';
        fixButton.className = 'btn btn-warning btn-sm';
        fixButton.style.position = 'fixed';
        fixButton.style.top = '50px';
        fixButton.style.right = '10px';
        fixButton.style.zIndex = '9999';
        fixButton.onclick = fixPredecessors;
        
        document.body.appendChild(debugButton);
        document.body.appendChild(fixButton);
    }
});