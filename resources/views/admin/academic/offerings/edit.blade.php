@extends('layouts.admin')

@php
    $pageTitle = 'تعديل المادة المسجلة';
    $pageSubtitle = 'تعديل بيانات المادة المسجلة';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.offerings.index') }}" class="btn btn-secondary">الرجوع إلى المواد المسجلة</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">تعديل المادة المسجلة</h2>

            <form method="POST" action="{{ route('admin.academic.offerings.update', $offering->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">المقرر</label>
                    <select name="course_id" required>
                        <option value="">اختر المقرر</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id', $offering->course_id) == $course->id ? 'selected' : '' }}>
                                {{ $course->name_ar }}{{ $course->code ? ' - ' . $course->code : '' }} - {{ $course->department?->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">العام الدراسي</label>
                    <input type="text" name="academic_year" value="{{ old('academic_year', $offering->academic_year) }}" placeholder="مثال: 2024-2025" required>
                </div>

                <div class="form-group">
                    <label class="form-label">الفصل الدراسي</label>
                    <select name="semester" required>
                        <option value="">اختر الفصل الدراسي</option>
                        <option value="first" {{ old('semester', $offering->semester) == 'first' ? 'selected' : '' }}>الفصل الدراسي الأول</option>
                        <option value="second" {{ old('semester', $offering->semester) == 'second' ? 'selected' : '' }}>الفصل الدراسي الثاني</option>
                        <option value="summer" {{ old('semester', $offering->semester) == 'summer' ? 'selected' : '' }}>الفصل الصيفي</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">الفرقة</label>
                    <input type="text" name="level" value="{{ old('level', $offering->level) }}" placeholder="مثال: الفرقة الثانية" required>
                </div>

                <div class="form-group">
                    <label class="form-label">القائم على التدريس</label>
                    <input type="text" name="instructor_name" value="{{ old('instructor_name', $offering->instructor_name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">الهيئة المعاونة</label>
                    <input type="text" name="assistant_name" value="{{ old('assistant_name', $offering->assistant_name) }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.academic.offerings.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection