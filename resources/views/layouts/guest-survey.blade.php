<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'منصة الاستبيانات' }}</title>
    <link rel="icon" type="image/x-icon" href="images/system-logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #28335f;
            --primary-dark: #1f294d;
            --primary-soft: #eef1ff;
            --background: #f5f7fb;
            --surface: #ffffff;
            --border: #e4e8f0;
            --text-main: #1f2a44;
            --text-muted: #6b7280;
            --shadow: 0 18px 50px rgba(31, 42, 68, 0.08);
            --success-bg: #ecfdf3;
            --success-text: #166534;
            --warning-bg: #fff8e1;
            --warning-text: #8d6e00;
            --danger-bg: #ffebee;
            --danger-text: #b71c1c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Alexandria', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(79,110,247,0.08), transparent 25%),
                radial-gradient(circle at bottom left, rgba(20,184,166,0.06), transparent 20%),
                var(--background);
            color: var(--text-main);
            padding: 20px;
        }

        .guest-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        .guest-header {
            background: linear-gradient(135deg, var(--primary) 0%, #3b4b88 100%);
            color: #fff;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .guest-header-inner {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .guest-logo {
            width: 84px;
            height: 84px;
            border-radius: 20px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .guest-logo img {
           
            height: 100%;
            object-fit: contain;
        }

        .guest-brand h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.4;
        }

        .guest-brand p {
            margin: 0;
            font-size: 15px;
            color: rgba(255,255,255,0.88);
            line-height: 1.9;
        }

        .guest-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .alert {
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 16px;
            line-height: 1.8;
            font-size: 14px;
        }

        .alert-danger {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid #ffcdd2;
        }

        .alert-warning {
            background: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid #ffe0b2;
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #bbf7d0;
        }

        .footer-note {
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 18px;
            line-height: 1.8;
        }

        @media (max-width: 700px) {
            .guest-header {
                padding: 18px;
            }

            .guest-header-inner {
                align-items: flex-start;
            }

            .guest-brand h1 {
                font-size: 22px;
            }

            .guest-brand p {
                font-size: 14px;
            }

            .guest-logo {
                width: 72px;
                height: 72px;
            }

            body {
                padding: 14px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="guest-page">
        <div class="guest-header">
            <div class="guest-header-inner">
                <div class="guest-logo">
                    <img src="{{ asset('images/system-logo.png') }}" style="width:75%;" alt="Survey Logo">
                </div>
                <div class="guest-logo">
                    <img src="{{ asset('images/university-logo.png') }}" alt="PSU Logo" >
                </div>
                <div class="guest-brand">
                    <h1 style="text-align: center;">{{ $survey->title }}</h1>
                    <div class="footer-note" style="color:white">
                    جامعة بورسعيد
                    </div>
                </div>
            </div>
        </div>

        @yield('content')

        <div class="footer-note">
            جميع الحقوق محفوظة © {{ config('app.name', 'منصة الاستبيانات') }}
            <br>
            تم التطوير بواسطة مركز نظم وتكنولوجيا المعلومات - جامعة بورسعيد
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>