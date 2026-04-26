<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'منصة الاستبيانات') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-brand">
            <div class="brand-top">
                <div class="brand-logos">
                    <div class="brand-logo">
                        <img src="{{ asset('images/university-logo.png') }}" alt="لوجو الجامعة">
                    </div>

                    <div class="brand-logo">
                        <img src="{{ asset('images/system-logo.png') }}" alt="لوجو السيستم">
                    </div>
                </div>

                <h1 class="brand-title">{{ config('app.name', 'منصة الاستبيانات') }} </h1>
                <p class="brand-subtitle">
                    نظام إلكتروني لإدارة الاستبيانات الجامعية وتحليل نتائجها بما يدعم الجودة واتخاذ القرار وتحسين العملية التعليمية.
                </p>

                <div class="brand-points">
                    <div class="brand-point">إدارة الاستبيانات  من واجهة واحدة.</div>
                    <div class="brand-point">متابعة النتائج والتقارير والتصدير بصيغ متعددة.</div>
                    <div class="brand-point">واجهة متجاوبة وصلاحيات منظمة للاستخدام الإداري.</div>
                </div>
            </div>

            <div class="brand-bottom">
                <div class="brand-footer">
                    مركز نظم وتكنولوجيا المعلومات - جامعة بورسعيد<br>
                    ISTC - Port Said University
                </div>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-card">
                @yield('content')
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>