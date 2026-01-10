@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Wait for jQuery to be loaded before executing
        (function() {
            function initInvoiceTemplates() {
                // Check if jQuery is loaded
                if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                    // Retry after a short delay
                    setTimeout(initInvoiceTemplates, 100);
                    return;
                }
                
                // Use jQuery safely
                jQuery(document).ready(function($) {
            // ============================================
             // 1️⃣ تفعيل السحب والإفلات (Drag & Drop)
            // ============================================
            var sortable = new Sortable(document.getElementById('sortable-columns'), {
                animation: 150,
                handle: '.fa-grip-vertical',
                onEnd: function() {
                    updateColumnOrder();
                }
            });

            // ============================================
             // 2️⃣ تحديث ترتيب الأعمدة بعد السحب
            // ============================================
            function updateColumnOrder() {
                $('#sortable-columns .column-item').each(function(index) {
                    var columnKey = $(this).data('column');
                    // تحديث قيمة الـ hidden input
                    $(this).find('.column-order-input').val(columnKey);
                });

                console.log('✅ Column order updated');
            }

            // ============================================
            // 3️⃣ تحديث عرض العمود عند تحريك السلايدر
            // ============================================
            $('.column-width-slider').on('input', function() {
                var columnKey = $(this).data('column');
                var width = $(this).val();
                $('#width_' + columnKey).text(width + '%');
            });

            // ============================================
             // 4️⃣ إظهار/إخفاء خيار "افتراضي" لأنواع الفواتير
            // ============================================

            // عند تحميل الصفحة - إظهار الخيارات المحددة مسبقاً
            $('.invoice-type-checkbox').each(function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).show();
                } else {
                    $('#default_' + typeId).hide();
                }
            });

            // عند تغيير الاختيار
            $('.invoice-type-checkbox').on('change', function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).slideDown(200);
                } else {
                    $('#default_' + typeId).slideUp(200);
                    $('#default_switch_' + typeId).prop('checked', false);
                }
            });

            // ============================================
             // 5️⃣ التحقق قبل الإرسال (Validation)
            // ============================================
            $('form').on('submit', function(e) {
                var checkedColumns = $('.column-checkbox:checked').length;
                var checkedTypes = $('.invoice-type-checkbox:checked').length;

                if (checkedColumns === 0) {
                    e.preventDefault();
                    alert('⚠️ يجب اختيار عمود واحد على الأقل');
                    return false;
                }

                if (checkedTypes === 0) {
                    e.preventDefault();
                    alert('⚠️ يجب اختيار نوع فاتورة واحد على الأقل');
                    return false;
                }

                // تحديث الترتيب قبل الإرسال
                updateColumnOrder();
                return true;
            });
                });
            }
            
            // Start initialization
            initInvoiceTemplates();
        })();
    </script>
@endpush
