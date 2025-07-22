<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Tahoma, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            width: 360px;
            padding: 40px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .login-box h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .login-box label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .login-box input[type="checkbox"] {
            margin-right: 6px;
        }

        .login-box button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #0056b3;
        }

        .login-box .links {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .error {
            color: red;
            font-size: 13px;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .success {
            color: green;
            font-size: 13px;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>تسجيل الدخول</h2>

    @if(session('status'))
        <div class="success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">البريد الإلكتروني</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">كلمة المرور</label>
        <input type="password" name="password" id="password" required>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label>
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            تذكرني
        </label>

        <button type="submit">تسجيل الدخول</button>

        <div class="links">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">هل نسيت كلمة المرور؟</a><br>
            @endif
            @if (Route::has('register'))
                ليس لديك حساب؟ <a href="{{ route('register') }}">سجل الآن</a>
            @endif
        </div>
    </form>
</div>

</body>
</html>
