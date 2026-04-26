@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الأقسام';
    $pageSubtitle = 'إضافة وعرض الأقسام وربطها بالكليات';

    $currentUser = auth()->user();
    $isUniversityAdmin = $currentUser?->role === 'university_admin';
    $isFacultyAdmin = $currentUser?->role === 'faculty_admin';
    $canManageDepartments = $isUniversityAdmin || $isFacultyAdmin;
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.academic.faculties.index') }}" class="btn btn-secondary">الكليات</a>
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">المقررات</a>
        <a href="{{ route('admin.academic.offerings.index') }}" class="btn btn-secondary">المواد المسجلة</a>
    </div>

    <div class="grid-2">
        @if($canManageDepartments)
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">إضافة قسم جديد</h2>

                    <form method="POST" action="{{ route('admin.academic.departments.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">الكلية</label>
                            <select name="faculty_id" required>
                                <option value="">اختر الكلية</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                        {{ $faculty->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم القسم بالعربية</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم القسم بالإنجليزية</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">إضافة القسم</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">صلاحيات إدارة الأقسام</h2>
                    <div class="empty-state" style="margin: 0;">
                        يمكنك عرض بيانات القسم فقط. إضافة أو تعديل أو حذف الأقسام متاح لأدمن الجامعة وأدمن الكلية.
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h2 class="section-title">ملخص سريع</h2>

                <div class="grid-2">
                    <div class="card stats-card">
                        <div class="stats-title">عدد الأقسام</div>
                        <div class="stats-value">{{ $departments->count() }}</div>
                    </div>

                    <div class="card stats-card">
                        <div class="stats-title">عدد الكليات</div>
                        <div class="stats-value">{{ $faculties->count() }}</div>
                    </div>
                </div>

                <div style="height: 12px;"></div>

                <div class="card stats-card">
                    <div class="stats-title">آخر قسم مضاف</div>
                    <div class="stats-value" style="font-size:18px;">
                        {{ $departments->first()?->name_ar ?? '—' }}
                    </div>
                    <div class="stats-note">
                        {{ $departments->first()?->faculty?->name_ar ?? '' }}
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
                    <th>اسم القسم بالعربية</th>
                    <th>اسم القسم بالإنجليزية</th>
                    @if($canManageDepartments)
                        <th>الإجراءات</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $department->name_ar }}</td>
                        <td>{{ $department->name_en ?: '—' }}</td>

                        @if($canManageDepartments)
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.academic.departments.edit', $department->id) }}" class="btn btn-secondary">
                                        تعديل
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.academic.departments.destroy', $department->id) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم؟');"
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
                        <td colspan="{{ $canManageDepartments ? 5 : 4 }}">
                            <div class="empty-state">لا توجد أقسام حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection