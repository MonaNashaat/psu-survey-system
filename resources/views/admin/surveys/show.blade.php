@extends('layouts.admin')

@php
    $pageTitle = $survey->title;
    $pageSubtitle = 'عرض تفاصيل الاستبيان والمحاور والأسئلة';
@endphp

@section('content')
    @php
        $currentUser = auth()->user();

        $semesterLabels = [
            'first' => 'الفصل الدراسي الأول',
            'second' => 'الفصل الدراسي الثاني',
            'summer' => 'الفصل الصيفي',
        ];

        $scopeLabel = match($survey->scope_level) {
            'university' => 'جامعة',
            'faculty' => 'كلية',
            'department' => 'قسم',
            default => '—',
        };

        $isCourseSurvey = !empty($survey->course_offering_id);
        $semesterName = $semesterLabels[$survey->courseOffering?->semester] ?? ($survey->semester ?? '—');
        $standaloneQuestions = $survey->questions->whereNull('survey_section_id');

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
            $canEdit = true;
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

    <div class="page-actions">
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">رجوع</a>

        @if($canEdit)
            <a href="{{ route('admin.surveys.edit', $survey->id) }}" class="btn btn-secondary">تعديل</a>
        @endif

        <a href="{{ route('surveys.show', $survey->id) }}" target="_blank" class="btn btn-secondary">فتح الرابط العام</a>
        <a href="{{ route('admin.surveys.results', $survey->id) }}" class="btn btn-primary">عرض النتائج</a>

        @if($currentUser->isUniversityAdmin())
            <a href="{{ route('admin.surveys.permissions', $survey->id) }}" class="btn btn-secondary">الصلاحيات</a>
        @endif

        @if($canEdit)
            <form method="POST" action="{{ route('admin.surveys.destroy', $survey->id) }}"
                  onsubmit="return confirm('هل أنت متأكدة من حذف هذا الاستبيان؟')"
                  style="display:inline;">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">حذف</button>
            </form>
        @endif
    </div>

    <div class="grid-2">
        <div class="card stats-card">
            <div class="stats-title">عدد الردود الحالية</div>
            <div class="stats-value">{{ $responsesCount }}</div>
            <div class="stats-note">
                @if($survey->expected_responses)
                    {{ $responsesCount }} / {{ $survey->expected_responses }}
                @else
                    غير محدد
                @endif
            </div>
        </div>

        <div class="card stats-card">
            <div class="stats-title">مستوى الاستبيان</div>
            <div class="stats-value">{{ $scopeLabel }}</div>
            <div class="stats-note">{{ $isCourseSurvey ? 'استبيان مرتبط بمقرر' : 'استبيان عام' }}</div>
        </div>
    </div>

    <div style="height: 16px;"></div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">بيانات الاستبيان</h2>

                <div class="meta-grid">
                    <div><strong>العنوان:</strong> {{ $survey->title }}</div>
                    <div><strong>الوصف:</strong> {{ $survey->description ?: '—' }}</div>
                    <div><strong>المستوى:</strong> {{ $scopeLabel }}</div>
                    <div><strong>نوع الربط:</strong> {{ $isCourseSurvey ? 'مقرر' : 'عام' }}</div>
                    <div>
                        <strong>الحالة:</strong>
                        @if($survey->is_active)
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-warning">غير نشط</span>
                        @endif
                    </div>
                    <div><strong>تعدد الردود:</strong> {{ $survey->allow_multiple_submissions ? 'مسموح' : 'غير مسموح' }}</div>
                    <div><strong>الحد الأقصى للردود:</strong> {{ $survey->expected_responses ?? 'غير محدد' }}</div>
                    <div><strong>إغلاق تلقائي عند بلوغ الحد:</strong> {{ $survey->auto_close_on_limit ? 'نعم' : 'لا' }}</div>
                    <div><strong>عدد المحاور:</strong> {{ $survey->sections->count() }}</div>
                    <div><strong>عدد الأسئلة المستقلة:</strong> {{ $standaloneQuestions->count() }}</div>
                    <div><strong>إجمالي الأسئلة:</strong> {{ $survey->questions->count() }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="section-title">الربط الأكاديمي</h2>

                <div class="meta-grid">
                    <div><strong>الكلية:</strong> {{ $surveyFaculty }}</div>
                    <div><strong>القسم:</strong> {{ $surveyDepartment }}</div>
                    <div><strong>المقرر:</strong> {{ $surveyCourse }}</div>
                    <div><strong>العام الدراسي:</strong> {{ $surveyAcademicYear }}</div>
                    <div><strong>الفصل الدراسي:</strong> {{ $semesterName }}</div>
                    <div><strong>الفرقة:</strong> {{ $survey->courseOffering?->level ?? $survey->level ?? '—' }}</div>
                    <div><strong>القائم على التدريس:</strong> {{ $survey->courseOffering?->instructor_name ?? '—' }}</div>
                    <div><strong>الهيئة المعاونة:</strong> {{ $survey->courseOffering?->assistant_name ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 16px;"></div>

    @forelse($survey->sections as $section)
        <div class="card section-card">
            <div class="card-body">
                <h2 class="section-title">{{ $section->title }}</h2>

                @foreach($section->questions as $question)
                    <div class="question-card">
                        <div class="question-title">
                            {{ $question->display_order }}. {{ $question->question_text }}
                        </div>

                        <div class="question-meta">
                            <span>
                                <strong>النوع:</strong>
                                @if($question->type === 'scale')
                                    تقييم 1-5
                                @elseif($question->type === 'mcq')
                                    اختيار من متعدد
                                @else
                                    نص مفتوح
                                @endif
                            </span>
                            <span><strong>الحالة:</strong> {{ $question->is_required ? 'إجباري' : 'اختياري' }}</span>
                        </div>

                        @if($question->options->count())
                            <ul class="options-list">
                                @foreach($question->options as $option)
                                    <li>{{ $option->option_text }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <div class="empty-state">لا توجد محاور مضافة لهذا الاستبيان.</div>
            </div>
        </div>
    @endforelse

    @if($standaloneQuestions->count())
        <div class="card section-card">
            <div class="card-body">
                <h2 class="section-title">أسئلة مستقلة</h2>

                @foreach($standaloneQuestions as $question)
                    <div class="question-card">
                        <div class="question-title">{{ $question->question_text }}</div>

                        <div class="question-meta">
                            <span>
                                <strong>النوع:</strong>
                                @if($question->type === 'scale')
                                    تقييم 1-5
                                @elseif($question->type === 'mcq')
                                    اختيار من متعدد
                                @else
                                    نص مفتوح
                                @endif
                            </span>
                            <span><strong>الحالة:</strong> {{ $question->is_required ? 'إجباري' : 'اختياري' }}</span>
                        </div>

                        @if($question->options->count())
                            <ul class="options-list">
                                @foreach($question->options as $option)
                                    <li>{{ $option->option_text }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 24px;
        line-height: 1.9;
    }

    .section-card {
        margin-bottom: 16px;
    }

    .question-card {
        border: 1px solid #e4e8f0;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 12px;
        background: #fafbff;
    }

    .question-title {
        font-weight: 800;
        margin-bottom: 8px;
        color: #28335f;
        line-height: 1.8;
    }

    .question-meta {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }

    .options-list {
        margin: 0;
        padding-right: 18px;
        line-height: 1.9;
    }

    @media (max-width: 768px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush