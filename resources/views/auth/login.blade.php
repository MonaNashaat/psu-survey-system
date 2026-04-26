@extends('layouts.auth')

@php
    $pageTitle = 'تسجيل الدخول | منصة الاستبيانات الأكاديمية';
@endphp

@section('content')
    <div class="auth-card-header">
        <h2>تسجيل الدخول</h2>
        <p>أدخل بياناتك للوصول إلى لوحة التحكم.</p>
    </div>

    @if (session('status'))
        <div class="status-box">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error-box">
            <ul style="margin:0; padding-right:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>

        <div class="remember-row">
            <label class="remember-box" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>تذكرني</span>
            </label>

            {{-- @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}">
                    نسيت كلمة المرور؟
                </a>
            @endif --}}
        </div>

        <button type="submit" class="auth-btn">
            دخول إلى النظام
        </button>
    </form>

    <div class="auth-footer-note">
        جميع الحقوق محفوظة © جامعة بورسعيد
    </div>
@endsection