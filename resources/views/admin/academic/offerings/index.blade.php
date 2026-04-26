@extends('layouts.admin')

@php
    $pageTitle = 'إدارة المواد المسجلة';
    $pageSubtitle = 'إضافة وعرض المواد المسجلة المرتبطة بالمقررات';

    $currentUser = auth()->user();
    $isUniversityAdmin = $currentUser?->role === 'university_admin';
    $isFacultyAdmin = $currentUser?->role === 'faculty_admin';
    $isDepartmentAdmin = $currentUser?->role === 'department_admin';
    $canManageOfferings = $isUniversityAdmin || $isFacultyAdmin || $isDepartmentAdmin;
@endphp

@section('content')
    @php
        $semesterLabels = [
            'first' => 'الفصل الدراسي الأول',
            'second' => 'الفصل الدراسي الثاني',
            'summer' => 'الفصل الصيفي',
        ];
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">الكليات</a>
        <a href="{{ route('admin.academic.departments.index') }}" class="btn btn-secondary">الأقسام</a>
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">المقررات</a>
    </div>

    <div class="grid-2">
        @if($canManageOfferings)
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">إضافة مادة مسجلة جديدة</h2>

                    <form method="POST" action="{{ route('admin.academic.offerings.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">المقرر</label>
                            <select name="course_id" required>
                                <option value="">اختر المقرر</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name_ar }}{{ $course->code ? ' - ' . $course->code : '' }} - {{ $course->department?->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">العام الدراسي</label>
                            <input type="text" name="academic_year" value="{{ old('academic_year') }}" placeholder="مثال: 2024-2025" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الفصل الدراسي</label>
                            <select name="semester" required>
                                <option value="">اختر الفصل الدراسي</option>
                                <option value="first" {{ old('semester') == 'first' ? 'selected' : '' }}>الفصل الدراسي الأول</option>
                                <option value="second" {{ old('semester') == 'second' ? 'selected' : '' }}>الفصل الدراسي الثاني</option>
                                <option value="summer" {{ old('semester') == 'summer' ? 'selected' : '' }}>الفصل الصيفي</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الفرقة</label>
                            <input type="text" name="level" value="{{ old('level') }}" placeholder="مثال: الفرقة الثانية" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">القائم على التدريس</label>
                            <input type="text" name="instructor_name" value="{{ old('instructor_name') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">الهيئة المعاونة</label>
                            <input type="text" name="assistant_name" value="{{ old('assistant_name') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">إضافة المادة</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">رفع المواد المسجلة من ملف</h2>
            
                    <div class="page-actions" style="margin-bottom: 16px;">
                        <a href="{{ asset('templates/offerings_template.xlsx') }}" class="btn btn-secondary" download>
                            تحميل Template Excel
                        </a>
                        {{-- <a href="{{ asset('templates/offerings_template.csv') }}" class="btn btn-secondary" download>
                            تحميل Template CSV
                        </a> --}}
                    </div>
            
                    <form method="POST" action="{{ route('admin.academic.offerings.import') }}" enctype="multipart/form-data">
                        @csrf
            
                        <div class="form-group">
                            <label class="form-label">ملف Excel</label>
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
            
                        <div class="form-group">
                            <div class="empty-state" style="margin:0; text-align:right;">
                                يجب أن يحتوي الملف على الأعمدة التالية:
                                <br>
                                <strong>course_code</strong>, <strong>academic_year</strong>, <strong>semester</strong>, <strong>level</strong>
                                <br>
                                <strong>instructor_name</strong>, <strong>assistant_name</strong>
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
                        <div class="stats-title">عدد المواد المسجلة</div>
                        <div class="stats-value">{{ $offerings->count() }}</div>
                    </div>

                    <div class="card stats-card">
                        <div class="stats-title">عدد المقررات</div>
                        <div class="stats-value">{{ $courses->count() }}</div>
                    </div>
                </div>

                <div style="height: 12px;"></div>

                <div class="card stats-card">
                    <div class="stats-title">آخر مادة مضافة</div>
                    <div class="stats-value" style="font-size:18px;">
                        {{ $offerings->first()?->course?->name_ar ?? '—' }}
                    </div>
                    <div class="stats-note">
                        {{ $offerings->first()?->academic_year ?? '' }}
                        @if($offerings->first()?->semester)
                            | {{ $semesterLabels[$offerings->first()?->semester] ?? $offerings->first()?->semester }}
                        @endif
                        @if($offerings->first()?->level)
                            | {{ $offerings->first()?->level }}
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
                    <th>المقرر</th>
                    <th>الكود</th>
                    <th>العام الدراسي</th>
                    <th>الفصل الدراسي</th>
                    <th>الفرقة</th>
                    <th>القائم على التدريس</th>
                    <th>الهيئة المعاونة</th>
                    @if($canManageOfferings)
                        <th>الإجراءات</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($offerings as $offering)
                    <tr>
                        <td>{{ $offering->id }}</td>
                        <td>{{ $offering->course?->department?->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $offering->course?->department?->name_ar ?? '—' }}</td>
                        <td>{{ $offering->course?->name_ar ?? '—' }}</td>
                        <td>{{ $offering->course?->code ?: '—' }}</td>
                        <td>{{ $offering->academic_year }}</td>
                        <td>{{ $semesterLabels[$offering->semester] ?? $offering->semester }}</td>
                        <td>{{ $offering->level }}</td>
                        <td>{{ $offering->instructor_name ?: '—' }}</td>
                        <td>{{ $offering->assistant_name ?: '—' }}</td>

                        @if($canManageOfferings)
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.academic.offerings.edit', $offering->id) }}" class="btn btn-secondary">
                                        تعديل
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.academic.offerings.destroy', $offering->id) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المادة المسجلة؟');"
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
                        <td colspan="{{ $canManageOfferings ? 11 : 10 }}">
                            <div class="empty-state">لا توجد طروحات دراسية حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection