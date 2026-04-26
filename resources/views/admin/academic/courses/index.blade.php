@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المقررات';
    $pageSubtitle = 'إضافة وعرض المقررات وربطها بالأقسام';

    $currentUser = auth()->user();
    $isUniversityAdmin = $currentUser?->role === 'university_admin';
    $isFacultyAdmin = $currentUser?->role === 'faculty_admin';
    $isDepartmentAdmin = $currentUser?->role === 'department_admin';

    $canManageCourses = $isUniversityAdmin || $isFacultyAdmin || $isDepartmentAdmin;
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">الكليات</a>
        <a href="{{ route('admin.academic.departments.index') }}" class="btn btn-secondary">الأقسام</a>
        <a href="{{ route('admin.academic.offerings.index') }}" class="btn btn-secondary">المواد المسجلة</a>
    </div>

    <div class="grid-2">
        @if($canManageCourses)
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">إضافة مقرر جديد</h2>

                    <form method="POST" action="{{ route('admin.academic.courses.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">القسم</label>
                            <select name="department_id" required>
                                <option value="">اختر القسم</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name_ar }} - {{ $department->faculty?->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">كود المقرر</label>
                            <input type="text" name="code" value="{{ old('code') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم المقرر بالعربية</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم المقرر بالإنجليزية</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">إضافة المقرر</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">رفع المقررات من ملف</h2>
            
                    <div class="page-actions" style="margin-bottom: 16px;">
                        <a href="{{ asset('templates/courses_template.xlsx') }}" class="btn btn-secondary" download>
                            تحميل Template Excel
                        </a>
                        {{-- <a href="{{ asset('templates/courses_template.csv') }}" class="btn btn-secondary" download>
                            تحميل Template CSV
                        </a> --}}
                    </div>
            
                    <form method="POST" action="{{ route('admin.academic.courses.import') }}" enctype="multipart/form-data">
                        @csrf
            
                        <div class="form-group">
                            <label class="form-label">ملف Excel</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
            
                        <div class="form-group">
                            <div class="empty-state" style="margin:0; text-align:right;">
                                يجب أن يحتوي الملف على الأعمدة التالية:
                                <br>
                                <strong>department_id</strong> أو <strong>department_name_ar</strong>
                                <br>
                                <strong>code</strong>, <strong>name_ar</strong>, <strong>name_en</strong>
                            </div>
                        </div>
            
                        <button type="submit" class="btn btn-primary">رفع الملف</button>
                    </form>
                </div>
            </div>
           
        @endif

        <div class="card">
            <div class="card-body">
                <h2 class="section-title">ملخص سريع</h2>

                <div class="grid-2">
                    <div class="card stats-card">
                        <div class="stats-title">عدد المقررات</div>
                        <div class="stats-value">{{ $courses->count() }}</div>
                    </div>

                    <div class="card stats-card">
                        <div class="stats-title">عدد الأقسام</div>
                        <div class="stats-value">{{ $departments->count() }}</div>
                    </div>
                </div>

                <div style="height: 12px;"></div>

                <div class="card stats-card">
                    <div class="stats-title">آخر مقرر مضاف</div>
                    <div class="stats-value" style="font-size:18px;">
                        {{ $courses->first()?->name_ar ?? '—' }}
                    </div>
                    <div class="stats-note">
                        {{ $courses->first()?->code ?? '' }}
                        @if($courses->first()?->department?->name_ar)
                            | {{ $courses->first()?->department?->name_ar }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 16px;"></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الكلية</th>
                    <th>القسم</th>
                    <th>كود المقرر</th>
                    <th>اسم المقرر بالعربية</th>
                    <th>اسم المقرر بالإنجليزية</th>
                    @if($canManageCourses)
                        <th>الإجراءات</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td>{{ $course->id }}</td>
                        <td>{{ $course->department?->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $course->department?->name_ar ?? '—' }}</td>
                        <td>{{ $course->code ?: '—' }}</td>
                        <td>{{ $course->name_ar }}</td>
                        <td>{{ $course->name_en ?: '—' }}</td>

                        @if($canManageCourses)
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.academic.courses.edit', $course->id) }}" class="btn btn-secondary">
                                        تعديل
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.academic.courses.destroy', $course->id) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا المقرر؟');"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManageCourses ? 7 : 6 }}">
                            <div class="empty-state">لا توجد مقررات حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection