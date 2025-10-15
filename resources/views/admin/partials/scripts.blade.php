 <!-- jQuery  -->
  <!-- ضع هذا العنصر في أي مكان في الصفحة (يفضل قبل نهاية الـ body) -->
<audio id="submit-sound" src="{{ asset('assets/wav/paper_sound.wav') }}"></audio>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // استهدف جميع النماذج في الصفحة
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            // شغل الصوت
            var audio = document.getElementById('submit-sound');
            if (audio) {
                audio.currentTime = 0; // إعادة الصوت للبداية
                audio.play();
            }
            // يمكنك إزالة السطر التالي إذا كنت لا تريد منع الإرسال الفعلي للنموذج
            // event.preventDefault();
        });
    });
});
</script>


 <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
 <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
 <script src="{{ asset('assets/js/metismenu.min.js') }}"></script>
 <script src="{{ asset('assets/js/waves.js') }}"></script>
 <script src="{{ asset('assets/js/feather.min.js') }}"></script>
 <script src="{{ asset('assets/js/simplebar.min.js') }}"></script>
 <script src="{{ asset('assets/js/moment.js') }}"></script>
 <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
 <script src="{{ asset('assets/plugins/apex-charts/apexcharts.min.js') }}"></script>
 <script src="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
 <script src="{{ asset('assets/plugins/jvectormap/jquery-jvectormap-us-aea-en.js') }}"></script>
 <script src="{{ asset('assets/pages/jquery.analytics_dashboard.init.js') }}"></script>
 <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

 <!-- Select2 Language Support -->
 @if(app()->getLocale() === 'ar')
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/ar.js"></script>
 @elseif(app()->getLocale() === 'tr')
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/tr.js"></script>
 @else
     <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/en.js"></script>
 @endif

 <!-- Tom Select JS -->
 <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <!-- App js -->
 <script src="{{ asset('assets/js/app.js') }}"></script>
 <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

 <!-- Livewire Scripts -->
 @livewireScripts

 <!-- Location Tracking Scripts -->
 <script src="{{ asset('assets/js/location-tracker.js') }}"></script>
 <script>
     // تهيئة التتبع عند تسجيل الدخول
     @auth
         document.addEventListener('DOMContentLoaded', async function() {
             console.log('LocationTracker: بدء تحميل نظام تتبع الموقع...');
             
             const googleApiKey = '{{ config("services.google.maps_api_key") }}';
             console.log('LocationTracker: Google API Key:', googleApiKey ? 'موجود' : 'غير موجود');
             
             if (typeof LocationTracker === 'undefined') {
                 console.error('LocationTracker: فئة LocationTracker غير موجودة!');
                 return;
             }
             
             const locationTracker = new LocationTracker();
             
             try {
                 console.log('LocationTracker: بدء تهيئة النظام...');
                 
                 // مسح أي تتبع سابق قد يكون بإحداثيات خاطئة
                 console.log('LocationTracker: مسح التتبع السابق لضمان دقة الموقع...');
                 localStorage.removeItem('location_tracking');
                 
                 // بدء تتبع جديد دائماً لضمان الحصول على الموقع الصحيح
                 console.log('LocationTracker: بدء تتبع جديد...');
                 await locationTracker.init(googleApiKey);
                 
                 console.log('LocationTracker: تم تهيئة نظام تتبع الموقع بنجاح');
             } catch (error) {
                 console.error('LocationTracker: فشل في تهيئة نظام تتبع الموقع:', error);
             }
         });
     @endauth
 </script>

 @stack('scripts')
