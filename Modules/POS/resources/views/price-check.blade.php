@extends('pos::layouts.master')

@push('styles')
<style>
    .price-check-container {
        min-height: 100vh;
        padding: 2rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .price-check-card {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .price-check-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .price-check-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }

    .price-check-body {
        padding: 2rem;
    }

    .barcode-input-container {
        margin-bottom: 2rem;
    }

    .barcode-input-wrapper {
        position: relative;
        max-width: 500px;
        margin: 0 auto;
    }

    .barcode-input {
        width: 100%;
        padding: 1.5rem;
        font-size: 1.5rem;
        text-align: center;
        border: 3px solid #667eea;
        border-radius: 15px;
        outline: none;
        transition: all 0.3s;
    }

    .barcode-input:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .barcode-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.5rem;
        color: #667eea;
    }

    .item-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 30px;
        padding: 4rem 3rem;
        margin-bottom: 2rem;
        display: none;
        text-align: center;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    }

    .item-info.show {
        display: block;
        animation: slideInScale 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes slideInScale {
        0% {
            opacity: 0;
            transform: scale(0.5) translateY(50px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .item-name {
        font-size: 4.5rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 2rem;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        animation: fadeInUp 0.8s ease-out 0.2s both;
        line-height: 1.2;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .price-display {
        font-size: 5.5rem;
        font-weight: 900;
        color: #ffd700;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: pulseGlow 2s ease-in-out infinite, fadeInUp 0.8s ease-out 0.4s both;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    @keyframes pulseGlow {
        0%, 100% {
            transform: scale(1);
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 30px rgba(255, 215, 0, 0.3);
        }
        50% {
            transform: scale(1.05);
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), 0 0 40px rgba(255, 215, 0, 0.5);
        }
    }

    .price-currency {
        font-size: 3rem;
        color: #ffffff;
        opacity: 0.9;
    }

    .price-value-number {
        animation: numberPop 0.6s ease-out 0.6s both;
    }

    @keyframes numberPop {
        0% {
            opacity: 0;
            transform: scale(0);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .error-message {
        background: #fed7d7;
        color: #c53030;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        margin-top: 1rem;
        display: none;
    }

    .error-message.show {
        display: block;
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    .loading-spinner {
        text-align: center;
        padding: 4rem 2rem;
        display: none;
        animation: fadeIn 0.3s ease-out;
    }

    .loading-spinner.show {
        display: block;
    }

    .spinner {
        width: 100px;
        height: 100px;
        margin: 0 auto 1.5rem;
        position: relative;
    }

    .spinner::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border: 8px solid rgba(102, 126, 234, 0.1);
        border-top: 8px solid #667eea;
        border-right: 8px solid #764ba2;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        box-shadow: 0 0 30px rgba(102, 126, 234, 0.3);
    }

    .spinner::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        border: 6px solid rgba(102, 126, 234, 0.1);
        border-top: 6px solid #764ba2;
        border-left: 6px solid #667eea;
        border-radius: 50%;
        animation: spinReverse 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes spinReverse {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(-360deg); }
    }

    .loading-spinner p {
        font-size: 1.5rem;
        font-weight: 600;
        color: #667eea;
        margin: 0;
        animation: pulseText 1.5s ease-in-out infinite;
    }

    @keyframes pulseText {
        0%, 100% {
            opacity: 0.7;
            transform: scale(1);
        }
        50% {
            opacity: 1;
            transform: scale(1.05);
        }
    }

    .back-button {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s;
    }

    .back-button:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .price-check-container {
            padding: 1rem;
        }

        .price-check-header h1 {
            font-size: 1.5rem;
        }

        .barcode-input {
            font-size: 1.2rem;
            padding: 1rem;
        }

        .item-name {
            font-size: 3rem;
        }

        .price-display {
            font-size: 4rem;
        }

        .price-currency {
            font-size: 2rem;
        }

        .spinner {
            width: 70px;
            height: 70px;
        }

        .spinner::after {
            width: 40px;
            height: 40px;
        }

        .loading-spinner p {
            font-size: 1.2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="price-check-container">
    <a href="{{ route('pos.create') }}" class="back-button" title="العودة">
        <i class="fas fa-arrow-right" style="font-size: 1.2rem; color: #667eea;"></i>
    </a>

    <div class="price-check-card">
        <div class="price-check-header">
            <h1><i class="fas fa-barcode me-2"></i> فحص السعر بالباركود</h1>
        </div>

        <div class="price-check-body">
            <div class="barcode-input-container">
                <div class="barcode-input-wrapper">
                    <input type="text" 
                           id="barcodeInput" 
                           class="barcode-input" 
                           placeholder="امسح الباركود أو اكتبه هنا..."
                           autocomplete="off"
                           autofocus>
                    <i class="fas fa-barcode barcode-icon"></i>
                </div>
            </div>

            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner"></div>
                <p>جاري البحث...</p>
            </div>

            <div class="error-message" id="errorMessage"></div>

            <div class="item-info" id="itemInfo">
                <div class="item-name" id="itemName"></div>
                <div class="price-display" id="priceDisplay">
                    <span class="price-currency">ر.س</span>
                    <span class="price-value-number" id="priceValue"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const barcodeInput = $('#barcodeInput');
        const loadingSpinner = $('#loadingSpinner');
        const errorMessage = $('#errorMessage');
        const itemInfo = $('#itemInfo');
        const itemName = $('#itemName');
        const priceValue = $('#priceValue');

        // التركيز على حقل الباركود عند تحميل الصفحة
        barcodeInput.focus();

        // البحث عند الضغط على Enter
        barcodeInput.on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                const barcode = $(this).val().trim();
                if (barcode.length > 0) {
                    searchPrice(barcode);
                }
            }
        });

        // مسح الحقل بعد البحث
        barcodeInput.on('input', function() {
            if ($(this).val().length === 0) {
                hideResults();
            }
        });

        function searchPrice(barcode) {
            // إخفاء النتائج السابقة
            hideResults();
            
            // إظهار مؤشر التحميل
            loadingSpinner.addClass('show');
            
            // البحث
            $.ajax({
                url: '{{ route("pos.api.price-check", ":barcode") }}'.replace(':barcode', encodeURIComponent(barcode)),
                method: 'GET',
                success: function(response) {
                    loadingSpinner.removeClass('show');
                    
                    if (response.success && response.item) {
                        displayItemInfo(response.item);
                        // مسح الحقل بعد النجاح
                        setTimeout(function() {
                            barcodeInput.val('').focus();
                        }, 100);
                    } else {
                        showError('حدث خطأ أثناء البحث');
                    }
                },
                error: function(xhr) {
                    loadingSpinner.removeClass('show');
                    
                    if (xhr.status === 404) {
                        showError(xhr.responseJSON?.message || 'لم يتم العثور على صنف بهذا الباركود');
                    } else {
                        showError('حدث خطأ أثناء الاتصال بالخادم');
                    }
                    
                    // مسح الحقل بعد الخطأ
                    setTimeout(function() {
                        barcodeInput.val('').focus();
                    }, 2000);
                }
            });
        }

        function displayItemInfo(item) {
            // عرض اسم الصنف
            itemName.text(item.name);
            
            // البحث عن السعر الأول المتاح
            let displayPrice = null;
            
            if (item.prices_by_unit && item.prices_by_unit.length > 0) {
                // البحث في أول وحدة تحتوي على أسعار
                for (let i = 0; i < item.prices_by_unit.length; i++) {
                    const unitData = item.prices_by_unit[i];
                    if (unitData.prices && unitData.prices.length > 0) {
                        // أخذ أول سعر (عادة السعر الأساسي)
                        displayPrice = unitData.prices[0].price;
                        break;
                    }
                }
            }
            
            // عرض السعر
            if (displayPrice !== null) {
                priceValue.text(formatPrice(displayPrice));
            } else {
                priceValue.text('غير متوفر');
            }
            
            // إظهار معلومات الصنف مع animation
            itemInfo.removeClass('show');
            setTimeout(function() {
                itemInfo.addClass('show');
            }, 10);
        }

        function showError(message) {
            errorMessage.text(message);
            errorMessage.addClass('show');
            
            setTimeout(function() {
                errorMessage.removeClass('show');
            }, 5000);
        }

        function hideResults() {
            itemInfo.removeClass('show');
            errorMessage.removeClass('show');
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('ar-SA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price);
        }

        // دعم اختصار لوحة المفاتيح للعودة
        $(document).on('keydown', function(e) {
            // Escape للعودة
            if (e.key === 'Escape') {
                window.location.href = '{{ route("pos.create") }}';
            }
        });
    });
</script>
@endpush
