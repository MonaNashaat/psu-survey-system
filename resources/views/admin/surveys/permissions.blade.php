@extends('layouts.admin')

@php
    $pageTitle = 'صلاحيات الاستبيان';
    $pageSubtitle = 'إدارة المستخدمين المسموح لهم بعرض نتائج هذا الاستبيان';
@endphp

@section('content')
    @php
        $scopeLabel = match($survey->scope_level) {
            'university' => 'جامعة',
            'faculty' => 'كلية',
            'department' => 'قسم',
            default => '—',
        };

        $surveyFaculty = $survey->courseOffering?->course?->department?->faculty?->name_ar
            ?? $survey->faculty?->name_ar
            ?? '—';

        $surveyDepartment = $survey->courseOffering?->course?->department?->name_ar
            ?? $survey->department?->name_ar
            ?? $survey->department_name
            ?? '—';

        $surveyCourse = $survey->courseOffering?->course?->name_ar
            ?? $survey->course_title
            ?? '—';

        $responsesCount = $survey->responses_count ?? $survey->responses()->count();
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">الرجوع إلى الاستبيان</a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">الاستبيانات</a>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">منح صلاحية جديدة</h2>

                <p style="margin-bottom:16px;">
                    <strong>الاستبيان:</strong> {{ $survey->title }}
                </p>

                <form method="POST" action="{{ route('admin.surveys.permissions.store', $survey->id) }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">المستخدم</label>
                        <select name="user_id" required>
                            <option value="">اختر المستخدم</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">نوع الصلاحية</label>
                        <select name="permission_type" required>
                            <option value="view_results">عرض النتائج</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">منح الصلاحية</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="section-title">ملخص سريع</h2>

                <div class="grid-2">
                    <div class="card stats-card">
                        <div class="stats-title">عدد الصلاحيات الحالية</div>
                        <div class="stats-value">{{ $survey->permissions->count() }}</div>
                    </div>

                    <div class="card stats-card">
                        <div class="stats-title">عدد الردود الحالية</div>
                        <div class="stats-value">{{ $responsesCount }}</div>
                    </div>
                </div>

                <div style="height: 12px;"></div>

                <div class="card stats-card">
                    <div class="stats-title">بيانات الاستبيان</div>
                    <div class="stats-value" style="font-size:18px;">
                        {{ $survey->title }}
                    </div>
                    <div class="stats-note" style="margin-top:10px; line-height:1.9;">
                        المستوى: {{ $scopeLabel }}<br>
                        الكلية: {{ $surveyFaculty }}<br>
                        القسم: {{ $surveyDepartment }}<br>
                        المقرر: {{ $surveyCourse }}
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
                    <th>المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور</th>
                    <th>الكلية</th>
                    <th>القسم</th>
                    <th>نوع الصلاحية</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($survey->permissions as $permission)
                    <tr>
                        <td>{{ $permission->user->name }}</td>
                        <td>{{ $permission->user->email }}</td>
                        <td>{{ $permission->user->role ?? '—' }}</td>
                        <td>{{ $permission->user->faculty?->name_ar ?? '—' }}</td>
                        <td>{{ $permission->user->department?->name_ar ?? '—' }}</td>
                        <td>
                            @if($permission->permission_type === 'view_results')
                                <span class="badge badge-success">عرض النتائج</span>
                            @else
                                <span class="badge badge-warning">{{ $permission->permission_type }}</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('admin.surveys.permissions.destroy', [$survey->id, $permission->id]) }}"
                                  onsubmit="return confirm('هل تريد حذف هذه الصلاحية؟')"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">لا توجد صلاحيات مضافة حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection