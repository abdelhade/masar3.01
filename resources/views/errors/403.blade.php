<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">
    <title>البحر الواسع 😅</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #fefcea, #f1daff);
            margin: 0;
            padding: 50px 20px;
            text-align: center;
            color: #333;
        }

        .animation {
            width: 250px;
            height: 250px;
            margin: 0 auto 30px;
            background-image: url("{{ asset('assets/images/404.gif') }}");
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        h1 {
            font-size: 5rem;
            color: #ff6b6b;
            margin: 0;
        }

        p {
            font-size: 1.6rem;
            margin: 20px 0;
            color: #444;
        }

        .dialect {
            font-size: 1.3rem;
            color: #6c757d;
            font-style: italic;
            margin-bottom: 40px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        a.button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4ecdc4;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1.1rem;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        a.button:hover {
            background-color: #38b3ac;
            transform: scale(1.05);
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 3rem;
            }

            p,
            .dialect {
                font-size: 1.1rem;
            }

            .animation {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>

<body>
    <div class="animation"></div>

    <h1>403</h1>
    <p>بتدخل في حاجه مش بتاعتك ليه يا عم 😠</p>

    <div class="dialect">
        "ارجع للمكان اللي جيت منه، ولا روح شوف وراك إيه 🤪"
    </div>

    <div class="buttons">
        <a href="{{ url('/dashboard') }}" class="button">ارجع للرئيسية</a>
        <a href="{{ url()->previous() }}" class="button">اتفرج بس متلعبش 😒</a>
    </div>
</body>

</html>