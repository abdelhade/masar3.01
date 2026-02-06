@extends('progress::layouts.auth')

@section('content')
<div class="login-container">
    
    <div class="animated-bg">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="floating-particles">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="container py-5 position-relative" style="z-index: 10;">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-10 col-lg-8">
                <div class="login-card-wrapper">
                    
                    <div class="login-brand">
                        <div class="brand-content">
                            <div class="logo-circle">
                                <i class="fas fa-chart-line fa-3x animate-float"></i>
                            </div>
                            <h1 class="brand-title mt-4">نظام إدارة المشاريع</h1>
                            <p class="brand-subtitle">تتبع تقدم مشاريعك بكفاءة واحترافية</p>
                            
                            <div class="features-list mt-5">
                                <div class="feature-item animate-slide-in" style="animation-delay: 0.2s;">
                                    <i class="fas fa-check-circle"></i>
                                    <span>إدارة متقدمة للمشاريع</span>
                                </div>
                                <div class="feature-item animate-slide-in" style="animation-delay: 0.4s;">
                                    <i class="fas fa-users"></i>
                                    <span>متابعة الفرق والموظفين</span>
                                </div>
                                <div class="feature-item animate-slide-in" style="animation-delay: 0.6s;">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>تقارير وإحصائيات مفصلة</span>
                                </div>
                                <div class="feature-item animate-slide-in" style="animation-delay: 0.8s;">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>أمان وحماية متقدمة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="login-form-card">
                        <div class="form-header text-center mb-4">
                            <div class="welcome-icon mb-3">
                                <i class="fas fa-door-open fa-3x animate-pulse"></i>
                            </div>
                            <h3 class="fw-bold mb-2">مرحباً بعودتك!</h3>
                            <p class="text-muted">سجل دخولك للمتابعة</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            
                            <div class="form-floating mb-3 input-animated">
                                <input id="email" 
                                       type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="البريد الإلكتروني"
                                       required 
                                       autocomplete="email" 
                                       autofocus>
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                                </label>
                                <div class="input-icon">
                                    <i class="fas fa-at"></i>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            
                            <div class="form-floating mb-3 input-animated">
                                <input id="password" 
                                       type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password" 
                                       placeholder="كلمة المرور"
                                       required 
                                       autocomplete="current-password">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>كلمة المرور
                                </label>
                                <div class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check custom-checkbox">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember"
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        <i class="fas fa-bookmark me-1"></i>تذكرني
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="forgot-link" href="{{ route('password.request') }}">
                                        <i class="fas fa-key me-1"></i>نسيت كلمة المرور؟
                                    </a>
                                @endif
                            </div>

                            
                            <button type="submit" class="btn btn-login w-100 btn-lg mb-3">
                                <span class="btn-text">
                                    <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                                </span>
                                <span class="btn-loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-2"></span>جاري التحميل...
                                </span>
                            </button>

                            
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1 text-success animate-pulse"></i>
                                    اتصال آمن ومشفر
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.login-container {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.animated-bg {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    animation: float 20s infinite ease-in-out;
}

.shape-1 {
    width: 500px;
    height: 500px;
    background: linear-gradient(45deg, #FF6B6B, #FFD93D);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(45deg, #6BCF7F, #4ECDC4);
    bottom: -10%;
    right: -10%;
    animation-delay: 7s;
}

.shape-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(45deg, #A8E6CF, #DCEDC1);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: 14s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(50px, -50px) rotate(90deg); }
    50% { transform: translate(0, -100px) rotate(180deg); }
    75% { transform: translate(-50px, -50px) rotate(270deg); }
}


.floating-particles span {
    position: absolute;
    width: 6px;
    height: 6px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    animation: rise 15s infinite ease-in;
}

.floating-particles span:nth-child(1) { left: 10%; animation-delay: 0s; }
.floating-particles span:nth-child(2) { left: 20%; animation-delay: 2s; }
.floating-particles span:nth-child(3) { left: 30%; animation-delay: 4s; }
.floating-particles span:nth-child(4) { left: 40%; animation-delay: 6s; }
.floating-particles span:nth-child(5) { left: 50%; animation-delay: 8s; }
.floating-particles span:nth-child(6) { left: 60%; animation-delay: 10s; }
.floating-particles span:nth-child(7) { left: 70%; animation-delay: 12s; }
.floating-particles span:nth-child(8) { left: 80%; animation-delay: 14s; }

@keyframes rise {
    0% {
        bottom: -10%;
        opacity: 0;
    }
    25% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
    100% {
        bottom: 110%;
        opacity: 0;
    }
}


.login-card-wrapper {
    display: flex;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.8s ease-out;
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


.login-brand {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-brand::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -100px;
    right: -100px;
    animation: rotate 30s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.brand-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.logo-circle {
    width: 120px;
    height: 120px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    backdrop-filter: blur(10px);
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.logo-circle i {
    color: white;
    filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
}

.animate-float {
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.brand-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.brand-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.features-list {
    text-align: right;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    margin-bottom: 1rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateX(-10px);
}

.feature-item i {
    font-size: 1.5rem;
}

.animate-slide-in {
    animation: slideIn 0.6s ease-out forwards;
    opacity: 0;
}

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}


.login-form-card {
    flex: 1;
    padding: 3rem;
    background: white;
}

.form-header .welcome-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.welcome-icon i {
    color: white;
}

.animate-pulse {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
    }
}


.input-animated {
    position: relative;
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-floating {
    position: relative;
}

.form-floating > .form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    height: 60px;
    padding-left: 3rem;
    transition: all 0.3s ease;
}

.form-floating > .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.form-floating > label {
    padding-left: 3rem;
    color: #6c757d;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 1.2rem;
    pointer-events: none;
    z-index: 5;
    transition: all 0.3s ease;
}

.form-control:focus ~ .input-icon {
    color: #764ba2;
    animation: bounce 0.5s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(-50%) scale(1); }
    50% { transform: translateY(-50%) scale(1.2); }
}


.password-toggle {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    z-index: 5;
    transition: all 0.3s ease;
}

.password-toggle:hover {
    color: #667eea;
    transform: translateY(-50%) scale(1.2);
}


.custom-checkbox .form-check-input {
    width: 1.5rem;
    height: 1.5rem;
    border: 2px solid #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
}

.custom-checkbox .form-check-input:checked {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    animation: checkBounce 0.3s ease;
}

@keyframes checkBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.custom-checkbox label {
    cursor: pointer;
    user-select: none;
    transition: color 0.3s ease;
}

.custom-checkbox .form-check-input:checked ~ label {
    color: #667eea;
    font-weight: 600;
}


.forgot-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.forgot-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    right: 0;
    background: #667eea;
    transition: width 0.3s ease;
}

.forgot-link:hover {
    color: #764ba2;
}

.forgot-link:hover::after {
    width: 100%;
    right: auto;
    left: 0;
}


.btn-login {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.1rem;
    color: white;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-login::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-login:hover::before {
    width: 300px;
    height: 300px;
}

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.btn-login:active {
    transform: translateY(-1px);
}


.btn-login.loading .btn-text {
    display: none;
}

.btn-login.loading .btn-loading {
    display: inline-block !important;
}


@media (max-width: 991px) {
    .login-brand {
        display: none;
    }
    
    .login-form-card {
        padding: 2rem;
    }
}

@media (max-width: 576px) {
    .login-form-card {
        padding: 1.5rem;
    }
    
    .brand-title {
        font-size: 1.5rem;
    }
    
    .form-floating > .form-control {
        height: 55px;
    }
}


@media (prefers-color-scheme: dark) {
    .login-form-card {
        background: #1a1a2e;
        color: white;
    }
    
    .form-floating > .form-control {
        background: #16213e;
        border-color: #0f3460;
        color: white;
    }
    
    .form-floating > label {
        color: #a0a0a0;
    }
}


@keyframes wiggle {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.shake-on-error {
    animation: wiggle 0.5s ease;
}


.form-control:focus {
    animation: glow 1.5s ease-in-out infinite;
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 5px rgba(102, 126, 234, 0.2),
                    0 0 10px rgba(102, 126, 234, 0.1);
    }
    50% {
        box-shadow: 0 0 10px rgba(102, 126, 234, 0.4),
                    0 0 20px rgba(102, 126, 234, 0.2);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    const submitBtn = form.querySelector('.btn-login');

    // Password Toggle
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            if (type === 'text') {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    }

    // Form Submit with Loading State
    form.addEventListener('submit', function(e) {
        // Add loading state to button
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });

    // Animate inputs on focus
    const inputs = form.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Add shake animation on error
    @if($errors->any())
        document.querySelector('.login-form-card').classList.add('shake-on-error');
        setTimeout(() => {
            document.querySelector('.login-form-card').classList.remove('shake-on-error');
        }, 500);
    @endif
});
</script>
@endsection
