@extends('layouts.admin')

@php
    $pageTitle = 'تعديل المقرر';
    $pageSubtitle = 'تعديل بيانات المقرر';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">الرجوع إلى المقررات</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">تعديل المقرر</h2>

            <form method="POST" action="{{ route('admin.academic.courses.update', $course->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">القسم</label>
                    <select name="department_id" required>
                        <option value="">اختر القسم</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $course->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name_ar }} - {{ $department->faculty?->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">كود المقرر</label>
                    <input type="text" name="code" value="{{ old('code', $course->code) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">اسم المقرر بالعربية</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $course->name_ar) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">اسم المقرر بالإنجليزية</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $course->name_en) }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection