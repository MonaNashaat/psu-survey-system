@extends('layouts.admin')

@php
    $pageTitle = 'تعديل الكلية';
    $pageSubtitle = 'تعديل بيانات الكلية';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">الرجوع إلى الكليات</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">تعديل الكلية</h2>

            <form method="POST" action="{{ route('admin.academic.faculties.update', $faculty->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">اسم الكلية بالعربية</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $faculty->name_ar) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">اسم الكلية بالإنجليزية</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $faculty->name_en) }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection