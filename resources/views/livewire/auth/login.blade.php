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

        $this->redirect(route('admin.dashboard'));
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

<!-- Log In page -->
<div class="container">
    <div class="row vh-100 d-flex justify-content-center">
        <div class="col-12 align-self-center">
            <div class="row">
                <div class="col-lg-5 mx-auto">
                    <div class="card">
                        <div class="card-body p-0 auth-header-box">
                            <div class="text-center p-3">
                                <a href="{{ url('/') }}" class="logo logo-admin">
                                    <img src="{{ asset('assets/images/logo-sm-dark.png') }}" height="50"
                                        alt="logo" class="auth-logo">
                                </a>
                                <h4 class="mt-3 mb-1 fw-semibold text-white font-18">خلينا نبدأ مع مسار</h4>
                                <p class="text-muted mb-0"> ... سجل الدخول </p>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content">
                                <div class="tab-pane active p-3" id="LogIn_Tab" role="tabpanel">
                                    <!-- Session Status -->
                                    <x-auth-session-status class="mb-4" :status="session('status')" />

                                    <form wire:submit="login">
                                        <!-- Email -->
                                        <div class="form-group mb-2">
                                            <label class="form-label">البريد الالكتروني </label>
                                            <div class="input-group">
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    wire:model="email" placeholder="email@example.com" required
                                                    autofocus>
                                            </div>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div class="form-group mb-2">
                                            <label class="form-label">كلمة المرور</label>
                                            <div class="input-group">
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    wire:model="password" placeholder="ادخل كلمة المرور " required>
                                            </div>
                                            @error('password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Remember Me -->
                                        <div class="form-group row my-3">
                                            <div class="col-sm-6">
                                                <div class="custom-control custom-switch switch-success">
                                                    <input type="checkbox" class="custom-control-input" id="remember_me"
                                                        wire:model="remember">
                                                    <label class="custom-control-label" for="remember_me">تذكرني
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <button class="btn btn-primary w-100 waves-effect waves-light"
                                                    type="submit">
                                                    تسجيل الدخول <i class="fas fa-sign-in-alt ms-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-light-alt text-center">
                            <span class="text-muted d-none d-sm-inline-block">
                                {{ config('app.name') }} © {{ date('Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
