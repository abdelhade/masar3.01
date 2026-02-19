// دالة موحدة لإظهار الرسائل (تدعم msg و alert)
function notify(message, type = 'success') {
    console.log(`POS Notification [${type}]: ${message}`);
    if (typeof window.msg === 'function') {
        window.msg(message, type);
    } else {
        alert(message);
    }
}

// زر تحديث الأصناف من السيرفر (يمسح القديم ويحمل الجديد)
$('#refreshItemsBtn').on('click', async function() {
    console.log('Refresh items button clicked');
    const btn = $(this);
    const originalHtml = btn.html();
    
    // التحقق من الاتصال
    if (!isOnline) {
        notify('يجب أن تكون متصلاً بالإنترنت لتحديث الأصناف', 'error');
        return;
    }

    if (!confirm('سيتم حذف البيانات المحلية وتحميل أحدث الأصناف من السيرفر، هل تود الاستمرار؟')) {
        return;
    }

    try {
        // تغيير حالة الزر
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> جاري التحديث...');
        console.log('Starting update process...');

        // 1. مسح البيانات القديمة
        itemsCache = {};
        if (db) {
            console.log('Clearing IndexedDB items...');
            await db.clearItems();
        }

        // 2. جلب البيانات الجديدة من السيرفر
        console.log('Fetching new items from server...');
        $.ajax({
            url: '{{ route("pos.api.all-items-details") }}',
            method: 'GET',
            success: async function(response) {
                console.log('Server response received', response);
                if (response && response.items) {
                    // تحديث الذاكرة المؤقتة
                    itemsCache = response.items;
                    
                    // تحديث IndexedDB
                    if (db) {
                        const itemsArray = Object.values(response.items);
                        console.log(`Saving ${itemsArray.length} items to IndexedDB`);
                        await db.saveItems(itemsArray);
                    }

                    notify('تم تحديث وتحميل الأصناف بنجاح', 'success');
                    
                    // إعادة تحميل المنتجات المعروضة
                    if (typeof loadAllProducts === 'function') {
                        loadAllProducts();
                    }
                } else {
                    console.error('Invalid response format', response);
                    notify('استجابة السيرفر غير صحيحة', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                notify('فشل تحديث الأصناف: ' + (error || 'خطأ في السيرفر'), 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
                console.log('Update process completed');
            }
        });

    } catch (error) {
        console.error('Critical Error during update:', error);
        notify('حدث خطأ فادح أثناء التحديث', 'error');
        btn.prop('disabled', false).html(originalHtml);
    }
});
