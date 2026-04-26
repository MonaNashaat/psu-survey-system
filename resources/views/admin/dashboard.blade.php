@extends('layouts.admin')

@php
    $pageTitle = 'لوحة المؤشرات';
    $pageSubtitle = 'نظرة عامة على أداء منظومة الاستبيانات';
@endphp

@section('content')
    <div class="grid-4">
        <div class="card stats-card">
            <div class="stats-title">إجمالي الاستبيانات</div>
            <div class="stats-value">{{ $totalSurveys }}</div>
        </div>

        <div class="card stats-card">
            <div class="stats-title">الاستبيانات النشطة</div>
            <div class="stats-value">{{ $activeSurveys }}</div>
        </div>

        <div class="card stats-card">
            <div class="stats-title">إجمالي الردود</div>
            <div class="stats-value">{{ $totalResponses }}</div>
        </div>

        <div class="card stats-card">
            <div class="stats-title">أحدث الاستبيانات</div>
            <div class="stats-value">{{ $latestSurveys->count() }}</div>
        </div>
    </div>
@endsection