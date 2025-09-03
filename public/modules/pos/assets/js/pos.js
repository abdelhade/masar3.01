// JavaScript للPOS
document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('pos-mode');
});

document.addEventListener('livewire:init', () => {
    Livewire.on('swal', (data) => {
        Swal.fire({
            title: data.title,
            text: data.text,
            icon: data.icon,
        }).then((result) => {
            if (data.icon === 'success') {
                // يمكن إضافة منطق إضافي هنا
            }
        });
    });

    Livewire.on('error', (data) => {
        Swal.fire({
            title: data.title,
            text: data.text,
            icon: data.icon,
        });
    });

    Livewire.on('open-print-window', (event) => {
        const url = event.url;
        const printWindow = window.open(url, '_blank');
        if (printWindow) {
            printWindow.onload = function() {
                printWindow.print();
            };
        } else {
            alert('يرجى السماح بفتح النوافذ المنبثقة في المتصفح للطباعة.');
        }
    });

    Livewire.on('prompt-create-item-from-barcode', (event) => {
        Swal.fire({
            title: 'صنف غير موجود!',
            text: `الباركود "${event.barcode}" غير مسجل. هل تريد إنشاء صنف جديد؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، قم بالإنشاء',
            cancelButtonText: 'إلغاء',
            input: 'text',
            inputLabel: 'الرجاء إدخال اسم الصنف الجديد',
            inputPlaceholder: 'اكتب اسم الصنف هنا...',
            inputValidator: (value) => {
                if (!value) {
                    return 'اسم الصنف مطلوب!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                // استخدام @this للوصول لمكون Livewire
                window.livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id')).call('createItemFromPrompt', result.value, event.barcode);
            }
        });
    });
});

// دعم الاختصارات للوحة المفاتيح
document.addEventListener('keydown', function(e) {
    // F1 للتركيز على البحث
    if (e.key === 'F1') {
        e.preventDefault();
        document.querySelector('.pos-search-input')?.focus();
    }
    
    // F2 للتركيز على الباركود
    if (e.key === 'F2') {
        e.preventDefault();
        document.querySelector('.barcode-input')?.focus();
    }
    
    // F9 للدفع والطباعة
    if (e.key === 'F9') {
        e.preventDefault();
        const payBtn = document.querySelector('.pos-btn.primary');
        if (payBtn && !payBtn.disabled) {
            payBtn.click();
        }
    }
    
    // ESC للإلغاء
    if (e.key === 'Escape') {
        e.preventDefault();
        document.querySelector('.pos-btn.danger')?.click();
    }
});

// تحديث الوقت كل دقيقة
setInterval(function() {
    const now = new Date();
    const timeStr = now.getFullYear() + '-' + 
                   String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(now.getDate()).padStart(2, '0') + ' ' + 
                   String(now.getHours()).padStart(2, '0') + ':' + 
                   String(now.getMinutes()).padStart(2, '0');
    
    const dateElement = document.querySelector('.current-date');
    if (dateElement) {
        dateElement.textContent = timeStr;
    }
}, 60000);
