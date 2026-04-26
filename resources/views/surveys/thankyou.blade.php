@extends('layouts.guest-survey')

@php
    $pageTitle = 'تم الإرسال';
@endphp

@section('content')

<div class="guest-card" style="padding:40px; text-align:center;">

    <div class="alert alert-success">
        تم إرسال الاستبيان بنجاح
    </div>

    <h2 style="margin-top:18px; color:#28335f; font-weight:800;">
        شكرًا لمشاركتك
    </h2>

    <p style="color:#6b7280; line-height:1.9; margin-top:10px;">
        تم استلام ردك بنجاح، وسيتم التعامل مع جميع البيانات بسرية تامة.
    </p>

    {{-- <div style="margin-top:24px;">
        <a href="{{ url('/') }}" 
           style="
                display:inline-block;
                background:#282c4e;
                color:#fff;
                padding:12px 24px;
                border-radius:10px;
                text-decoration:none;
                font-weight:bold;
           ">
            العودة للصفحة الرئيسية
        </a> --}}
    </div>

</div>

@endsection