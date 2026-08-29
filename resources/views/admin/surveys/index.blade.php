@extends('layouts.admin')

@php
    $pageTitle = 'إدارة الاستبيانات';
    $pageSubtitle = 'عرض وإدارة الاستبيانات الجامعية';
@endphp

@section('content')
    @php
        $currentUser = auth()->user();
    @endphp

        @if(

        $currentUser->isUniversityAdmin()

        || $currentUser->isPresidencyAdmin()

        || $currentUser->isFacultyAdmin()

        || $currentUser->isDepartmentAdmin()

        )
        <div class="page-actions">
            <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary">إنشاء استبيان جديد</a>
        </div>
    @endif
    @if(auth()->user()->isUniversityAdmin() || auth()->user()->isFacultyAdmin() || auth()->user()->isDepartmentAdmin())
    <div class="page-actions">
        <a href="{{ route('admin.surveys.export.active') }}" class="btn btn-primary">
            تصدير الاستبيانات المفعلة
        </a>
    </div>
    @endif
    
    @if(auth()->user()->isDepartmentAdmin())
        <div class="page-actions">
            <a href="{{ route('admin.surveys.bulk.create') }}" class="btn btn-primary">إنشاء استبيانات جماعيًا</a>
        </div>
    @endif
    

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>مستوى الاستبيان</th>
                    <th>نوع الربط</th>
                    <th>الكلية</th>
                    <th>القسم</th>
                    <th>المقرر</th>
                    <th>العام الدراسي</th>
                    <th>الحالة</th>
                    <th>الحد الأقصى للردود</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($surveys as $survey)
                    @php
                        $isCourseSurvey = !empty($survey->course_offering_id);

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

                        $surveyAcademicYear = $survey->courseOffering?->academic_year
                            ?? $survey->academic_year
                            ?? '—';

                        $responsesCount = $survey->responses_count ?? $survey->responses()->count();

                        $canEdit = false;

                        if ($currentUser->isUniversityAdmin()) {
                            $canEdit = (
                                $survey->scope_level === 'university'
                                && $survey->survey_owner === \App\Models\Survey::OWNER_QUALITY_CENTER
                            );
                        } elseif ($currentUser->isPresidencyAdmin()) {
                            $canEdit = (
                                $survey->scope_level === 'university'
                                && $survey->survey_owner === \App\Models\Survey::OWNER_PRESIDENCY
                            );
                        } elseif ($currentUser->isFacultyAdmin()) {
                            $canEdit = (
                                ($survey->scope_level === 'faculty' && $survey->faculty_id === $currentUser->faculty_id) ||
                                ($survey->scope_level === 'department' && $survey->faculty_id === $currentUser->faculty_id)
                            );
                        } elseif ($currentUser->isDepartmentAdmin()) {
                            $canEdit = (
                                $survey->scope_level === 'department' &&
                                $survey->department_id === $currentUser->department_id
                            );
                        }
                    @endphp

                    <tr>
                        <td>{{ $survey->title }}</td>

                        <td>
                            @if($survey->scope_level === 'university')
                                @if($survey->survey_owner === \App\Models\Survey::OWNER_PRESIDENCY)
                                    <span class="badge badge-success">المكتب الفني لرئيس الجامعة</span>
                                @else
                                    <span class="badge badge-success">جامعة / مركز الجودة</span>
                                @endif
                            @elseif($survey->scope_level === 'faculty')
                                <span class="badge badge-warning">كلية</span>
                            @else
                                <span class="badge badge-secondary">قسم</span>
                            @endif
                        </td>

                        <td>
                            @if($isCourseSurvey)
                                <span class="badge badge-success">مقرر</span>
                            @else
                                <span class="badge badge-warning">عام</span>
                            @endif
                        </td>

                        <td>{{ $surveyFaculty }}</td>
                        <td>{{ $surveyDepartment }}</td>
                        <td>{{ $surveyCourse }}</td>
                        <td>{{ $surveyAcademicYear }}</td>

                        <td>
                            @if($survey->is_active)
                                <span class="badge badge-success">نشط</span>
                            @else
                                <span class="badge badge-warning">غير نشط</span>
                            @endif

                            @if($survey->expected_responses && $responsesCount >= $survey->expected_responses)
                                <br><small style="color:#b45309;">تم بلوغ الحد</small>
                            @endif
                        </td>

                        <td>
                            @if($survey->expected_responses)
                                {{ $responsesCount }} / {{ $survey->expected_responses }}
                                @if($survey->auto_close_on_limit)
                                    <br><small style="color:#6b7280;">إغلاق تلقائي</small>
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <div class="page-actions" style="margin:0;">
                                @if(

                                    $currentUser->isUniversityAdmin()

                                    || $currentUser->isPresidencyAdmin()

                                    || $currentUser->isFacultyAdmin()

                                    || $currentUser->isDepartmentAdmin()

                                )
                                    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">تفاصيل</a>

                                    @if($canEdit)
                                        <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-secondary">تعديل</a>
                                    @endif

                                    <a href="{{ route('admin.surveys.results', $survey->id) }}" class="btn btn-primary">النتائج</a>

                                    @if($currentUser->isUniversityAdmin())
                                        <a href="{{ route('admin.surveys.permissions', $survey->id) }}" class="btn btn-secondary">الصلاحيات</a>
                                    @endif
                                @else
                                    <a href="{{ route('admin.surveys.results', $survey->id) }}" class="btn btn-primary">عرض النتائج</a>
                                    <a href="{{ route('admin.surveys.export.excel', $survey->id) }}" class="btn btn-success">Excel</a>
                                    <a href="{{ route('admin.surveys.export.pdf', $survey->id) }}" class="btn btn-secondary">PDF</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">لا توجد استبيانات حتى الآن</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection