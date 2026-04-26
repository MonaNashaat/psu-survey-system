@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الكليات';
    $pageSubtitle = 'إضافة وعرض الكليات داخل النظام';
    $currentUser = auth()->user();
    $isUniversityAdmin = $currentUser?->role === 'university_admin';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">لوحة المؤشرات</a>
        <a href="{{ route('admin.academic.departments.index') }}" class="btn btn-secondary">الأقسام</a>
        <a href="{{ route('admin.academic.courses.index') }}" class="btn btn-secondary">المقررات</a>
        <a href="{{ route('admin.academic.offerings.index') }}" class="btn btn-secondary">المواد المسجلة</a>
    </div>

    <div class="grid-2">
        @if($isUniversityAdmin)
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">إضافة كلية جديدة</h2>

                    <form method="POST" action="{{ route('admin.academic.faculties.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">اسم الكلية بالعربية</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">اسم الكلية بالإنجليزية</label>
                            <input type="text" name="name_en" value="{{ old('name_en') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">إضافة الكلية</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body">
                    <h2 class="section-title">صلاحيات إدارة الكليات</h2>
                    <div class="empty-state" style="margin: 0;">
                        إدارة الكليات الكاملة متاحة لأدمن الجامعة فقط.
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h2 class="section-title">ملخص سريع</h2>

                <div class="grid-2">
                    <div class="card stats-card">
                        <div class="stats-title">عدد الكليات</div>
                        <div class="stats-value">{{ $faculties->count() }}</div>
                    </div>

                    <div class="card stats-card">
                        <div class="stats-title">آخر إضافة</div>
                        <div class="stats-value" style="font-size:18px;">
                            {{ $faculties->first()?->name_ar ?? '—' }}
                        </div>
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
                    <th>الاسم بالعربية</th>
                    <th>الاسم بالإنجليزية</th>
                    @if($isUniversityAdmin)
                        <th>الإجراءات</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($faculties as $faculty)
                    <tr>
                        <td>{{ $faculty->id }}</td>
                        <td>{{ $faculty->name_ar }}</td>
                        <td>{{ $faculty->name_en ?: '—' }}</td>

                        @if($isUniversityAdmin)
                            <td>
                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('admin.academic.faculties.edit', $faculty->id) }}" class="btn btn-secondary">
                                        تعديل
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.academic.faculties.destroy', $faculty->id) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه الكلية؟');"
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
                        <td colspan="{{ $isUniversityAdmin ? 4 : 3 }}">
                            <div class="empty-state">لا توجد كليات حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection