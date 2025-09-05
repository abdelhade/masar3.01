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

new #[Layout('layouts.dastone-auth')] class extends Component {
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

        $this->redirect(route('admin.dashboard'), navigate: true);
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

<div>
    <title>مسار لادارة المشاريع</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            /* overflow: hidden;  حذف لمنع مشاكل في الموبايل */
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='7' cy='7' r='7'/%3E%3Ccircle cx='53' cy='7' r='7'/%3E%3Ccircle cx='30' cy='30' r='7'/%3E%3Ccircle cx='7' cy='53' r='7'/%3E%3Ccircle cx='53' cy='53' r='7'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: float 20s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% {
                transform: translateX(-50px) translateY(-50px);
            }

            100% {
                transform: translateX(50px) translateY(50px);
            }
        }

        /* تم إزالة الـ container القديم */

        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 550px;
            min-width: 0;
            margin: 0 auto;
        }

        .modern-login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.6s ease-out;
            width: 100%;
        }

        .modern-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
        }

        .modern-logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .modern-logo:hover {
            transform: translateY(-5px);
        }

        .modern-logo img {
            width: 100px;
            height: 100px;
            border-radius: 40%;
        }

        .modern-title {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .modern-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            font-weight: 400;
            position: relative;
            z-index: 2;
        }

        .modern-form {
            padding: 50px 40px;
        }

        .modern-form-group {
            margin-bottom: 30px;
            position: relative;
            animation: slideUp 0.6s ease-out;
        }

        .modern-form-group:nth-child(1) {
            animation-delay: 0.1s;
        }

        .modern-form-group:nth-child(2) {
            animation-delay: 0.2s;
        }

        .modern-form-group:nth-child(3) {
            animation-delay: 0.3s;
        }

        .modern-form-group:nth-child(4) {
            animation-delay: 0.4s;
        }

        .modern-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .modern-input-container {
            position: relative;
        }

        .modern-input {
            width: 100%;
            padding: 18px 22px 18px 55px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 1.1rem;
            background: #f8fafc;
            transition: all 0.3s ease;
            outline: none;
            color: #2d3748;
            font-family: 'Cairo', sans-serif;
            line-height: 1.5;
        }

        .modern-input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .modern-input.is-invalid {
            border-color: #e53e3e;
            background: #fed7d7;
        }

        .modern-input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.2rem;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .modern-input:focus~.modern-input-icon {
            color: #667eea;
        }

        .modern-error {
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .modern-remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            animation: slideUp 0.6s ease-out;
            animation-delay: 0.3s;
        }

        .modern-checkbox {
            position: relative;
            display: inline-block;
        }

        .modern-checkbox input {
            opacity: 0;
            position: absolute;
        }

        .modern-checkmark {
            width: 20px;
            height: 20px;
            background: #e2e8f0;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modern-checkbox input:checked+.modern-checkmark {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .modern-checkmark::after {
            content: '';
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .modern-checkbox input:checked+.modern-checkmark::after {
            display: block;
        }

        .modern-remember-label {
            color: #4a5568;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        .modern-button {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-family: 'Cairo', sans-serif;
            animation: slideUp 0.6s ease-out;
            animation-delay: 0.4s;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .modern-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
            z-index: 0;
        }

        .modern-button>* {
            position: relative;
            z-index: 2;
        }

        .modern-button:hover::before {
            left: 100%;
        }

        .modern-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .modern-button:active {
            transform: translateY(0);
        }

        .modern-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .modern-footer {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            color: #718096;
            font-size: 0.85rem;
            animation: slideUp 0.6s ease-out;
            animation-delay: 0.5s;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .modern-login-card {
                border-radius: 20px;
            }

            .modern-header {
                padding: 40px 25px;
            }

            .modern-form {
                padding: 40px 25px;
            }

            .modern-title {
                font-size: 1.6rem;
            }

            .modern-input {
                padding: 16px 20px 16px 50px;
                font-size: 1rem;
            }

            .modern-input-icon {
                left: 18px;
                font-size: 1.1rem;
            }

            .modern-button {
                padding: 16px 20px;
                font-size: 1.1rem;
                min-height: 55px;
            }
        }

        @media (max-width: 480px) {
            .modern-header {
                padding: 30px 20px;
            }

            .modern-form {
                padding: 30px 20px;
            }

            .modern-title {
                font-size: 1.4rem;
            }

            .modern-input {
                padding: 14px 18px 14px 45px;
                font-size: 0.95rem;
            }

            .modern-input-icon {
                left: 16px;
                font-size: 1rem;
            }

            .modern-button {
                padding: 14px 18px;
                font-size: 1rem;
                min-height: 50px;
            }

            .modern-form-group {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 350px) {
            .modern-header {
                padding: 18px 8px;
            }

            .modern-form {
                padding: 14px 8px;
            }

            .modern-title {
                font-size: 1.1rem;
            }

            .modern-input {
                padding: 10px 8px 10px 30px;
                font-size: 0.85rem;
            }

            .modern-button {
                padding: 10px 8px;
                font-size: 0.85rem;
                min-height: 40px;
            }

            .modern-form-group {
                margin-bottom: 15px;
            }
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <div class="auth-container">
            <div class="modern-login-card">
                <!-- Header -->
                <div class="modern-header">
                    <div class="modern-logo">
                        <img src="{{ asset('assets/images/masarlogo.jpg') }}" alt="{{ __('logo') }}" class="">
                    </div>
                    <h1 class="modern-title">مسار لادارة المشاريع</h1>
                    <p class="modern-subtitle">سجل الدخول للمتابعة</p>
                </div>

                <!-- Form Body -->
                <div class="modern-form">
                    <form wire:submit.prevent="login" autocomplete="on">
                        <!-- Email -->
                        <div class="modern-form-group">
                            <label class="modern-label" for="email">البريد الإلكتروني</label>
                            <div class="modern-input-container">
                                <input type="email" id="email" class="modern-input @error('email') is-invalid @enderror"
                                    placeholder="email@example.com" required wire:model="email" autocomplete="email">
                                <i class="fas fa-envelope modern-input-icon"></i>
                            </div>
                            @error('email')
                                <div class="modern-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>{{ __('يرجى إدخال بريد إلكتروني صحيح') }}</span>
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="modern-form-group">
                            <label class="modern-label" for="password">كلمة المرور</label>
                            <div class="modern-input-container">
                                <input type="password" id="password" class="modern-input" placeholder="ادخل كلمة المرور"
                                    required wire:model="password" autocomplete="current-password">
                                <i class="fas fa-lock modern-input-icon"></i>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="modern-remember">
                            <div class="modern-checkbox">
                                <input type="checkbox" id="remember" wire:model="remember">
                                <span class="modern-checkmark"></span>
                            </div>
                            <label class="modern-remember-label" for="remember">
                                {{ __('تذكرني') }}
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="modern-button" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="login">
                                تسجيل الدخول
                                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                            </span>
                            <span wire:loading wire:target="login">
                                جارِ تسجيل الدخول...
                                <span class="loading-spinner"></span>
                            </span>
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modern-footer">
                    <span>نظام مسار © {{ date('Y') }}</span>
                </div>
            </div>
        </div>
</div>