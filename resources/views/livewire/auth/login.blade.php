<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    protected $rules = [
        'email' => 'required|string|email',
        'password' => 'required|string',
    ];

    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectRoute('admin.dashboard');
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

@push('styles')
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endpush

<div>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 200% 200%;
            animation: gradient-shift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cairo', Tahoma, sans-serif;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* خلفية متحركة خفيفة */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            animation: background-float 20s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes background-float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(30px, -50px) rotate(120deg);
            }
            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        /* دوائر متحركة في الخلفية */
        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            animation: float-circle 15s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes float-circle {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(-100px, 100px) scale(1.1);
            }
        }

        /* Floating particles في الخلفية */
        .particle {
            position: fixed;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
            animation: particle-rise 1.5s ease-out forwards;
            z-index: 1;
        }

        @keyframes particle-rise {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(0);
                opacity: 0;
            }
        }

        /* Floating shapes في الخلفية */
        .floating-shape {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            pointer-events: none;
            z-index: 0;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation: float-1 20s ease-in-out infinite;
        }

        .floating-shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 70%;
            left: 80%;
            animation: float-2 18s ease-in-out infinite;
            animation-delay: -5s;
        }

        .floating-shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 40%;
            left: 85%;
            animation: float-3 22s ease-in-out infinite;
            animation-delay: -10s;
        }

        .floating-shape:nth-child(4) {
            width: 50px;
            height: 50px;
            top: 80%;
            left: 15%;
            animation: float-1 16s ease-in-out infinite;
            animation-delay: -8s;
        }

        @keyframes float-1 {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(30px, -30px) rotate(90deg);
            }
            50% {
                transform: translate(60px, 0) rotate(180deg);
            }
            75% {
                transform: translate(30px, 30px) rotate(270deg);
            }
        }

        @keyframes float-2 {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(-50px, -50px) scale(1.2);
            }
        }

        @keyframes float-3 {
            0%, 100% {
                transform: translate(0, 0);
            }
            33% {
                transform: translate(-40px, 40px);
            }
            66% {
                transform: translate(-80px, -40px);
            }
        }

        .login-container {
            width: 100%;
            max-width: 950px;
            padding: 25px;
            position: relative;
            z-index: 10;
            animation: card-entrance 0.6s ease-out;
        }

        @keyframes card-entrance {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.2s ease;
            position: relative;
        }

        .login-card:hover {
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.3);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #7272ff 0%, #5050d8 100%);
            padding: 3rem 3rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .logo-container {
            width: 110px;
            height: 110px;
            margin: 0 auto 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .logo-container:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.3);
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .app-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .app-subtitle {
            font-size: 1.15rem;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }

        .card-body-custom {
            padding: 3rem 4rem;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.65rem;
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .form-label:hover {
            color: #7272ff;
        }

        .form-control {
            width: 100%;
            padding: 1.1rem 1.35rem;
            color: #000;
            font-size: 1.05rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .form-control:hover {
            border-color: #7272ff;
        }

        .form-control:focus {
            background: #f0f9ff;
            border-color: #7272ff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(114, 114, 255, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 2rem;
            padding: 0.65rem 0;
        }

        .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
            accent-color: #7272ff;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            color: #666;
            font-size: 1.05rem;
        }

        .form-check-label:hover {
            color: #7272ff;
        }

        .btn-login {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #7272ff 0%, #5050d8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(114, 114, 255, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(114, 114, 255, 0.4);
            background: linear-gradient(135deg, #8484ff 0%, #6060e8 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 1rem;
            margin-top: 0.65rem;
            display: block;
            background: rgba(220, 53, 69, 0.1);
            padding: 0.65rem 1rem;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }

        .card-footer-custom {
            background: #f8f9fa;
            padding: 1.25rem;
            text-align: center;
            color: #6c757d;
            font-size: 1rem;
            border-top: 1px solid #dee2e6;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
            margin-right: 0.65rem;
        }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple-effect 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .login-container {
                max-width: 800px;
                padding: 20px;
            }
            
            .card-body-custom {
                padding: 2.5rem 3rem;
            }
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 650px;
                padding: 18px;
            }
            
            .card-header-custom {
                padding: 2.5rem 2rem;
            }
            
            .card-body-custom {
                padding: 2.25rem 2.5rem;
            }
            
            .logo-container {
                width: 95px;
                height: 95px;
            }
            
            .app-title {
                font-size: 1.75rem;
            }
            
            .app-subtitle {
                font-size: 1.05rem;
            }

            .form-label {
                font-size: 1.05rem;
            }

            .form-control {
                padding: 1rem 1.2rem;
                font-size: 1rem;
            }

            .btn-login {
                padding: 1.1rem;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
                padding: 12px;
            }
            
            .card-header-custom {
                padding: 2rem 1.5rem;
            }
            
            .card-body-custom {
                padding: 2rem 1.75rem;
            }
            
            .logo-container {
                width: 85px;
                height: 85px;
            }
            
            .app-title {
                font-size: 1.5rem;
            }
            
            .app-subtitle {
                font-size: 0.95rem;
            }
            
            .form-label {
                font-size: 1rem;
            }

            .form-control {
                padding: 0.95rem 1.1rem;
                font-size: 0.98rem;
            }

            .form-check-input {
                width: 1.15rem;
                height: 1.15rem;
            }

            .form-check-label {
                font-size: 1rem;
            }
            
            .btn-login {
                padding: 1rem;
                font-size: 1.05rem;
            }

            .invalid-feedback {
                font-size: 0.95rem;
            }

            .card-footer-custom {
                padding: 1rem;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 420px) {
            .login-container {
                padding: 10px;
            }

            .card-header-custom {
                padding: 1.75rem 1.25rem;
            }

            .card-body-custom {
                padding: 1.75rem 1.5rem;
            }

            .logo-container {
                width: 75px;
                height: 75px;
            }

            .app-title {
                font-size: 1.35rem;
            }

            .app-subtitle {
                font-size: 0.9rem;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-label {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }

            .form-control {
                padding: 0.85rem 1rem;
                font-size: 0.95rem;
            }

            .btn-login {
                padding: 0.95rem;
                font-size: 1rem;
            }
        }
    </style>

    <!-- Floating shapes في الخلفية -->
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>
    <div class="floating-shape"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="card-header-custom">
                <div class="logo-container">
                    <img src="{{ asset('assets/images/masarlogo.jpg') }}" alt="Logo">
                </div>
                <h1 class="app-title">مسار لإدارة المشاريع</h1>
                <p class="app-subtitle">سجل الدخول للمتابعة</p>
            </div>

            <!-- Body -->
            <div class="card-body-custom">
                <form wire:submit.prevent="login" autocomplete="on">
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">البريد الإلكتروني</label>
                        <input 
                            type="email" 
                            id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="email@example.com" 
                            required 
                            wire:model="email" 
                            autocomplete="email"
                            dir="ltr">
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label" for="password">كلمة المرور</label>
                        <input 
                            type="password" 
                            id="password" 
                            class="form-control" 
                            placeholder="••••••••"
                            required 
                            wire:model="password" 
                            autocomplete="current-password">
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="remember" 
                            wire:model="remember">
                        <label class="form-check-label" for="remember">
                            تذكرني
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="login">
                            تسجيل الدخول
                        </span>
                        <span wire:loading wire:target="login" class="d-flex align-items-center justify-content-center">
                            <span class="spinner-border spinner-border-sm"></span>
                            جارِ تسجيل الدخول...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="card-footer-custom">
                نظام مسار © {{ date('Y') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ripple effect على الزر فقط
            const btnLogin = document.querySelector('.btn-login');
            if (btnLogin) {
                btnLogin.addEventListener('click', function(e) {
                    createRipple(e, this);
                    // إضافة بعض الـ particles عند الضغط
                    createParticles(e.clientX, e.clientY, 8);
                });
            }

            // دالة Ripple بسيطة
            function createRipple(event, element) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                
                const rect = element.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = event.clientX - rect.left - size / 2;
                const y = event.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                
                element.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            }

            // دالة Particles مبسطة
            function createParticles(x, y, count) {
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    
                    const angle = (Math.PI * 2 * i) / count;
                    const velocity = 40 + Math.random() * 30;
                    const size = 4 + Math.random() * 3;
                    
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.left = x + 'px';
                    particle.style.top = y + 'px';
                    
                    const translateX = Math.cos(angle) * velocity;
                    const translateY = Math.sin(angle) * velocity;
                    particle.style.transform = `translate(${translateX}px, ${translateY}px)`;
                    
                    document.body.appendChild(particle);
                    
                    setTimeout(() => particle.remove(), 1500);
                }
            }
        });
    </script>
</div>
