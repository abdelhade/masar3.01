@extends('progress::layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header text-center text-white rounded-top-4"
                     style="background: linear-gradient(135deg, #4e73df, #1cc88a);">
                    <h4 class="mb-0 fw-bold">{{ __('Create Your Account') }}</h4>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">{{ __('Full Name') }}</label>
                            <input id="name" type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                                  >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ __('Email Address') }}</label>
                            <input id="email" type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required autocomplete="email"
                                  >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-3">
                            <label for="position" class="form-label fw-semibold">{{ __('general.position') }}</label>
                            <input id="position" type="text" 
                                   class="form-control form-control-lg @error('position') is-invalid @enderror" 
                                   name="position" value="{{ old('position') }}" required
                                 >
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">{{ __('general.phone') }}</label>
                            <input id="phone" type="text" 
                                   class="form-control form-control-lg @error('phone') is-invalid @enderror" 
                                   name="phone" value="{{ old('phone') }}" required
                              >
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
                            <input id="password" type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   name="password" required autocomplete="new-password"
                                  >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        
                        <div class="mb-3">
                            <label for="password-confirm" class="form-label fw-semibold">{{ __('Confirm Password') }}</label>
                            <input id="password-confirm" type="password" 
                                   class="form-control form-control-lg" 
                                   name="password_confirmation" required autocomplete="new-password"
>
                        </div>

                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg text-white"
                                    style="background: linear-gradient(135deg, #4e73df, #1cc88a); border: none;">
                                {{ __('Register') }}
                            </button>
                        </div>

                        
                        <div class="text-center mt-3">
                            <span>{{ __('Already have an account?') }}</span>
                            <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">
                                {{ __('Login') }}
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
