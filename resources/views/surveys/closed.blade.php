@extends('layouts.guest-survey')

@php
    $pageTitle = 'تم إغلاق الاستبيان';
@endphp

@section('content')
<div class="guest-card" style="padding:40px; text-align:center;">
    <div class="alert alert-danger">
        تم إغلاق الاستبيان
    </div>

    <h2 style="margin-top:18px; color:#28335f; font-weight:800;">
        هذا الاستبيان لم يعد متاحًا
    </h2>

    <p style="color:#6b7280; line-height:1.9; margin-top:10px;">
        {{ $survey->title ?? 'الاستبيان' }} لم يعد متاحًا،
        إما لأنه تم الوصول إلى الحد الأقصى من الردود
        أو لأنه تم إغلاقه من قبل الإدارة.
    </p>
</div>
@endsection