@extends('layouts.admin')

@php
    $pageTitle = 'تعديل القسم';
    $pageSubtitle = 'تعديل بيانات القسم';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.departments.index') }}" class="btn btn-secondary">الرجوع إلى الأقسام</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">تعديل القسم</h2>

            <form method="POST" action="{{ route('admin.academic.departments.update', $department->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">الكلية</label>
                    <select name="faculty_id" required>
                        <option value="">اختر الكلية</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" {{ old('faculty_id', $department->faculty_id) == $faculty->id ? 'selected' : '' }}>
                                {{ $faculty->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">اسم القسم بالعربية</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $department->name_ar) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">اسم القسم بالإنجليزية</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $department->name_en) }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.academic.departments.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection