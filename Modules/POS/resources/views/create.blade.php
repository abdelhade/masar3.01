@extends('pos::layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('modules/pos/assets/css/pos.css') }}">
@endpush

@section('content')
<div class="pos-create-page">
    <div class="pos-header-navigation">
        <a href="{{ route('pos.index') }}" class="back-btn">
            <i class="fas fa-arrow-right"></i>
            <span>العودة لنظام نقاط البيع</span>
        </a>
    </div>
    
    @livewire('modules.pos.http.livewire.create-pos-transaction-form')
</div>

<style>
    .pos-create-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    body.pos-mode {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    /* إخفاء شريط التمرير الجانبي إذا كان موجوداً */
    .pos-create-page .sidebar,
    .pos-create-page .main-sidebar,
    .pos-create-page .control-sidebar {
        display: none !important;
    }

    /* تأكد من أن المحتوى يأخذ كامل العرض */
    .pos-create-page .content-wrapper {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
</style>

<script>
    // تفعيل وضع POS
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('pos-fullscreen');
        
        // إخفاء عناصر الواجهة الأساسية للنظام
        const elementsToHide = [
            '.main-header',
            '.main-sidebar', 
            '.control-sidebar',
            'nav',
            '.navbar'
        ];
        
        elementsToHide.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                element.style.display = 'none';
            });
        });
        
        // جعل المحتوى يأخذ كامل الشاشة
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.style.marginLeft = '0';
            contentWrapper.style.marginRight = '0';
            contentWrapper.style.padding = '0';
        }
    });

    // التأكد من أن الصفحة تعمل في وضع ملء الشاشة
    document.addEventListener('keydown', function(e) {
        // F11 للدخول/الخروج من وضع ملء الشاشة
        if (e.key === 'F11') {
            e.preventDefault();
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }
    });
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('modules/pos/assets/js/pos.js') }}"></script>
@endpush
@endsection
