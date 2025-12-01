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

 <!-- App js (Legacy - only if not using Vite) -->
 {{-- Note: Vite assets (including app.js) are loaded in head.blade.php via @vite --}}
 {{-- This legacy app.js is kept for backward compatibility but should not conflict with Vite --}}
 {{-- <script src="{{ asset('assets/js/app.js') }}"></script> --}}

<script>
    // تحسينات الـ Sidebar
    document.addEventListener('DOMContentLoaded', function() {
        // تفعيل Feather Icons أولاً
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        const sidebarLinks = document.querySelectorAll('.left-sidenav-menu li > a');
        
        // Ripple effect على الـ links
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Ripple animation
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.5);
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.right = (rect.width - x - size) + 'px';
                ripple.style.top = y + 'px';
                
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Particle effect على الأيقونات
        const menuIcons = document.querySelectorAll('.menu-icon');
        menuIcons.forEach(icon => {
            icon.parentElement.addEventListener('mouseenter', function(e) {
                const rect = icon.getBoundingClientRect();
                createMiniParticles(rect.left + rect.width / 2, rect.top + rect.height / 2, 3);
            });
        });

        function createMiniParticles(x, y, count) {
            for (let i = 0; i < count; i++) {
                const particle = document.createElement('div');
                particle.style.cssText = `
                    position: fixed;
                    width: 4px;
                    height: 4px;
                    background: #667eea;
                    border-radius: 50%;
                    pointer-events: none;
                    z-index: 9999;
                    animation: particleFly 0.8s ease-out forwards;
                `;
                
                const angle = (Math.PI * 2 * i) / count;
                const velocity = 20 + Math.random() * 20;
                
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                particle.style.setProperty('--tx', Math.cos(angle) * velocity + 'px');
                particle.style.setProperty('--ty', Math.sin(angle) * velocity + 'px');
                
                document.body.appendChild(particle);
                setTimeout(() => particle.remove(), 800);
            }
        }

        // Add animation for particles
        if (!document.getElementById('sidebar-animations')) {
            const style = document.createElement('style');
            style.id = 'sidebar-animations';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                @keyframes particleFly {
                    0% {
                        transform: translate(0, 0) scale(1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(var(--tx), var(--ty)) scale(0);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // تفعيل الأيقونات مرة إضافية
        setTimeout(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
    });

    // تفعيل عند تحميل الصفحة كاملة
    window.addEventListener('load', function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // تأثيرات الـ Navbar
    function initNavbarEffects() {
        const navLinks = document.querySelectorAll('.topbar .nav-link');
        
        navLinks.forEach(link => {
            // Ripple على الـ links
            link.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(114, 114, 255, 0.4);
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                this.style.position = 'relative';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Particles على زر القائمة
        const menuBtn = document.querySelector('.button-menu-mobile');
        if (menuBtn) {
            menuBtn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = rect.left + rect.width / 2;
                const y = rect.top + rect.height / 2;
                
                for (let i = 0; i < 8; i++) {
                    const particle = document.createElement('div');
                    particle.style.cssText = `
                        position: fixed;
                        width: 5px;
                        height: 5px;
                        background: #7272ff;
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 9999;
                        left: ${x}px;
                        top: ${y}px;
                    `;
                    
                    const angle = (Math.PI * 2 * i) / 8;
                    const velocity = 20 + Math.random() * 25;
                    const tx = Math.cos(angle) * velocity;
                    const ty = Math.sin(angle) * velocity;
                    
                    particle.animate([
                        { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                        { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
                    ], {
                        duration: 800,
                        easing: 'ease-out'
                    });
                    
                    document.body.appendChild(particle);
                    setTimeout(() => particle.remove(), 800);
                }
            });
        }
    }

    // تفعيل تأثيرات الـ navbar
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initNavbarEffects, 200);
    });
</script>
 <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

 <!-- Livewire Scripts -->
 {{-- Livewire scripts must be loaded after Vite assets (which include Alpine.js) --}}
 {{-- Vite assets are loaded in head.blade.php via @vite directive --}}
 @livewireScripts

 {{-- Stack for additional scripts from components --}}
 @stack('scripts')

 {{-- Ensure Alpine.js is ready before any component scripts --}}
 <script>
     document.addEventListener('DOMContentLoaded', function() {
         // Wait for Alpine.js to be ready
         if (typeof window.Alpine !== 'undefined') {
             // Alpine.js is loaded, ensure employeeManager is registered
             if (window.Alpine && !window.Alpine.data('employeeManager')) {
                 console.warn('Alpine.js employeeManager component not found. Make sure Vite assets are loaded.');
             }
         } else {
             console.warn('Alpine.js is not loaded. Make sure Vite assets are loaded in head.');
         }
     });
 </script>
