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
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
@endpush

<div>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cairo', Tahoma, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Particles في الخلفية */
        .particle {
            position: fixed;
            width: 8px;
            height: 8px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            animation: particle-rise 2s ease-out forwards;
            z-index: 1;
        }

        @keyframes particle-rise {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-150px) scale(0);
                opacity: 0;
            }
        }

        .login-container {
            width: 100%;
            max-width: 380px;
            padding: 15px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .login-card:active {
            transform: scale(0.98);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #7272ff 0%, #5050d8 100%);
            padding: 1.75rem 1.5rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .card-header-custom:active::after {
            width: 500px;
            height: 500px;
        }

        .logo-container {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            z-index: 2;
        }

        .logo-container:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .logo-container:active {
            transform: scale(0.95);
            animation: wiggle 0.5s ease;
        }

        @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .logo-container img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .app-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            position: relative;
            z-index: 2;
        }

        .app-subtitle {
            font-size: 0.9rem;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }

        .card-body-custom {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            color: #333;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-label:hover {
            color: #7272ff;
            transform: translateX(-3px);
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            border: 1px solid #7272ff;
            border-radius: 8px;
            transition: all 0.3s;
            position: relative;
        }

        .form-control:focus {
            background-color: #e3f68e;
            border-color: #5050d8;
            outline: none;
            box-shadow: 0 0 0 3px rgba(114, 114, 255, 0.1);
            transform: translateY(-2px);
        }

        .form-control:active {
            transform: scale(0.99);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .form-check-input {
            width: 1.1rem;
            height: 1.1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .form-check-input:active {
            transform: scale(0.8);
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            color: #666;
            transition: color 0.3s;
        }

        .form-check-label:hover {
            color: #7272ff;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #7272ff 0%, #5050d8 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(114, 114, 255, 0.5);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-login:active::before {
            width: 400px;
            height: 400px;
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }

        .card-footer-custom {
            background: #f8f9fa;
            padding: 0.8rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.8rem;
            border-top: 1px solid #dee2e6;
        }

        .spinner-border-sm {
            width: 0.9rem;
            height: 0.9rem;
            border-width: 0.15em;
            margin-right: 0.5rem;
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

        @media (max-width: 576px) {
            .login-container {
                padding: 10px;
                max-width: 350px;
            }
            .card-header-custom {
                padding: 1.5rem 1.25rem;
            }
            .card-body-custom {
                padding: 1.25rem;
            }
            .app-title {
                font-size: 1.25rem;
            }
        }
    </style>

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
            // Ripple effect على الزر
            const btnLogin = document.querySelector('.btn-login');
            if (btnLogin) {
                btnLogin.addEventListener('click', function(e) {
                    createRipple(e, this);
                    createParticles(e.clientX, e.clientY, 12);
                });
            }

            // Particles على اللوجو
            const logo = document.querySelector('.logo-container');
            if (logo) {
                logo.addEventListener('click', function(e) {
                    createParticles(e.clientX, e.clientY, 15);
                });
            }

            // Particles على الـ Header
            const header = document.querySelector('.card-header-custom');
            if (header) {
                header.addEventListener('click', function(e) {
                    createParticles(e.clientX, e.clientY, 8);
                });
            }

            // تأثير على الـ inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function(e) {
                    createParticles(e.target.offsetLeft + e.target.offsetWidth / 2, 
                                  e.target.offsetTop + e.target.offsetHeight / 2, 5);
                });
            });

            // تأثير على الـ checkbox
            const checkbox = document.querySelector('.form-check-input');
            if (checkbox) {
                checkbox.addEventListener('change', function(e) {
                    if (this.checked) {
                        const rect = this.getBoundingClientRect();
                        createParticles(rect.left + rect.width / 2, rect.top + rect.height / 2, 8);
                    }
                });
            }

            // دالة Ripple
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

            // دالة Particles
            function createParticles(x, y, count) {
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    
                    const angle = (Math.PI * 2 * i) / count;
                    const velocity = 30 + Math.random() * 50;
                    const size = 4 + Math.random() * 4;
                    
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.left = x + 'px';
                    particle.style.top = y + 'px';
                    particle.style.transform = `translate(${Math.cos(angle) * velocity}px, ${Math.sin(angle) * velocity}px)`;
                    
                    document.body.appendChild(particle);
                    
                    setTimeout(() => particle.remove(), 2000);
                }
            }

            // تأثير على الـ Labels
            const labels = document.querySelectorAll('.form-label');
            labels.forEach(label => {
                label.addEventListener('click', function() {
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                });
            });
        });
    </script>
</div>
