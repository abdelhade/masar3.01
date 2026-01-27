    // تعريف دالة msg في النطاق العام
    window.msg = function(message, type = 'success') {
        const bgColor = type === 'success' ? '#28a745' : 
                        type === 'error' ? '#dc3545' : 
                        type === 'warning' ? '#ffc107' : '#17a2b8';
        const icon = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        const toast = $(`
            <div class="toast-notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: ${bgColor}; color: white; padding: 1rem 2rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 0.5rem; min-width: 300px;">
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    };
    
    // تعريف محلي أيضاً للتوافق
    function msg(message, type = 'success') {
        window.msg(message, type);
    }
    
    // تعريف showToast للتوافق مع الكود القديم
    window.showToast = window.msg;
    function showToast(message, type = 'success') {
        window.msg(message, type);
    }
