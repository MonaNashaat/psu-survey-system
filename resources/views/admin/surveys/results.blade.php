@extends('layouts.admin')

@php
    $pageTitle = 'نتائج الاستبيان';
    $pageSubtitle = 'عرض وتحليل نتائج الاستبيان';
@endphp

@section('content')
    @php
        $semesterLabels = [
            'first' => 'الفصل الدراسي الأول',
            'second' => 'الفصل الدراسي الثاني',
            'summer' => 'الفصل الصيفي',
        ];

        $isCourseSurvey = !empty($survey->course_offering_id);
        $semesterName = $semesterLabels[$survey->courseOffering?->semester] ?? '-';
        $standaloneQuestions = $survey->questions->whereNull('survey_section_id');
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">تفاصيل الاستبيان</a>
        <a href="{{ route('surveys.show', $survey->id) }}" target="_blank" class="btn btn-secondary">فتح الرابط العام</a>
        <a href="{{ route('admin.surveys.export.excel', $survey->id) }}" class="btn btn-success">تصدير Excel</a>
        {{-- <a href="{{ route('admin.surveys.export.pdf', $survey->id) }}" class="btn btn-secondary">تصدير PDF</a> --}}
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">بيانات الاستبيان</h2>

                <div class="grid-2">
                    <div>
                        <p><strong>العنوان:</strong> {{ $survey->title }}</p>
                        <p><strong>الوصف:</strong> {{ $survey->description ?: '—' }}</p>
                        <p><strong>عدد الردود:</strong> <span class="badge badge-success">{{ $responsesCount }}</span></p>
                        <p><strong>النوع:</strong> {{ $isCourseSurvey ? 'استبيان مرتبط بمادة' : 'استبيان عام' }}</p>
                    </div>

                    <div>
                        @if($isCourseSurvey)
                            <p><strong>الكلية:</strong> {{ $survey->courseOffering?->course?->department?->faculty?->name_ar ?? '-' }}</p>
                            <p><strong>القسم:</strong> {{ $survey->courseOffering?->course?->department?->name_ar ?? ($survey->department_name ?? '-') }}</p>
                            <p><strong>المقرر:</strong> {{ $survey->courseOffering?->course?->name_ar ?? ($survey->course_title ?? '-') }}</p>
                            <p><strong>كود المقرر:</strong> {{ $survey->courseOffering?->course?->code ?? '-' }}</p>
                            <p><strong>الفصل الدراسي:</strong> {{ $semesterName }}</p>
                            <p><strong>الفرقة:</strong> {{ $survey->courseOffering?->level ?? ($survey->level ?? '-') }}</p>
                            <p><strong>العام الدراسي:</strong> {{ $survey->courseOffering?->academic_year ?? ($survey->academic_year ?? '-') }}</p>
                            <p><strong>القائم على التدريس:</strong> {{ $survey->courseOffering?->instructor_name ?? '-' }}</p>
                            <p><strong>الهيئة المعاونة:</strong> {{ $survey->courseOffering?->assistant_name ?? '-' }}</p>
                        @else
                            <div class="empty-state" style="padding: 10px 0;">
                                هذا استبيان عام وغير مرتبط بمادة مسجلة.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 16px;"></div>

    @foreach($survey->sections as $section)
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
                <h2 class="section-title">{{ $section->title }}</h2>

                @foreach($section->questions as $question)
                    @php
                        $stats = $questionStats[$question->id] ?? null;
                    @endphp

                    <div style="border:1px solid #e4e8f0; border-radius:14px; padding:16px; margin-bottom:14px; background:#fafbff;">
                        <div style="font-weight:700; margin-bottom:10px;">
                            {{ $question->display_order }}. {{ $question->question_text }}
                        </div>

                        @if($stats && in_array($stats['type'], ['scale', 'mcq']))
                            <div style="margin-bottom:10px; color:#6b7280;">
                                <div><strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}</div>
                                @if(!is_null($stats['average']))
                                    <div><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}</div>
                                @endif
                            </div>

                            <div class="table-wrap" style="box-shadow:none;">
                                <table style="min-width:auto;">
                                    <thead>
                                        <tr>
                                            <th>الاختيار</th>
                                            <th>عدد المرات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['distribution'] as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>
                                                <td>{{ $item['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($stats && $stats['type'] === 'text')
                            <div style="margin-bottom:10px; color:#6b7280;">
                                <strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}
                            </div>

                            @forelse($stats['comments'] as $comment)
                                <div style="background:#fff; border:1px solid #e4e8f0; border-radius:12px; padding:12px; margin-bottom:8px;">
                                    {{ $comment }}
                                </div>
                            @empty
                                <div class="empty-state" style="padding:12px;">لا توجد تعليقات حتى الآن.</div>
                            @endforelse
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($standaloneQuestions->count())
        <div class="card">
            <div class="card-body">
                <h2 class="section-title">أسئلة إضافية</h2>

                @foreach($standaloneQuestions as $question)
                    @php
                        $stats = $questionStats[$question->id] ?? null;
                    @endphp

                    <div style="border:1px solid #e4e8f0; border-radius:14px; padding:16px; margin-bottom:14px; background:#fafbff;">
                        <div style="font-weight:700; margin-bottom:10px;">
                            {{ $question->question_text }}
                        </div>

                        @if($stats && $stats['type'] === 'text')
                            <div style="margin-bottom:10px; color:#6b7280;">
                                <strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}
                            </div>

                            @forelse($stats['comments'] as $comment)
                                <div style="background:#fff; border:1px solid #e4e8f0; border-radius:12px; padding:12px; margin-bottom:8px;">
                                    {{ $comment }}
                                </div>
                            @empty
                                <div class="empty-state" style="padding:12px;">لا توجد تعليقات حتى الآن.</div>
                            @endforelse
                        @elseif($stats && in_array($stats['type'], ['scale', 'mcq']))
                            <div style="margin-bottom:10px; color:#6b7280;">
                                <div><strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}</div>
                                @if(!is_null($stats['average']))
                                    <div><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}</div>
                                @endif
                            </div>

                            <div class="table-wrap" style="box-shadow:none;">
                                <table style="min-width:auto;">
                                    <thead>
                                        <tr>
                                            <th>الاختيار</th>
                                            <th>عدد المرات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['distribution'] as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>
                                                <td>{{ $item['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
@extends('layouts.admin')

@php
    $pageTitle = 'نتائج الاستبيان';
    $pageSubtitle = 'تحليل الردود والإجابات الخاصة بالاستبيان';
@endphp

@section('content')
    @php
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

        $semesterName = $semesterLabels[$survey->courseOffering?->semester] ?? ($survey->semester ?? '—');

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

        $isCourseSurvey = !empty($survey->course_offering_id);
        $responsesProgress = $survey->expected_responses ? ($responsesCount . ' / ' . $survey->expected_responses) : 'غير محدد';
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">رجوع</a>
        <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">تفاصيل الاستبيان</a>
        <a href="{{ route('surveys.show', $survey->id) }}" class="btn btn-secondary" target="_blank">فتح الرابط العام</a>
        <a href="{{ route('admin.surveys.export.excel', $survey->id) }}" class="btn btn-success">تصدير Excel</a>
        <a href="{{ route('admin.surveys.export.pdf', $survey->id) }}" class="btn btn-secondary">تصدير PDF</a>
    </div>

    <div class="grid-2">
        <div class="card stats-card">
            <div class="stats-title">عدد الردود</div>
            <div class="stats-value">{{ $responsesCount }}</div>
            <div class="stats-note">{{ $responsesProgress }}</div>
        </div>

        <div class="card stats-card">
            <div class="stats-title">مستوى الاستبيان</div>
            <div class="stats-value">{{ $scopeLabel }}</div>
            <div class="stats-note">{{ $isCourseSurvey ? 'استبيان مرتبط بمقرر' : 'استبيان عام' }}</div>
        </div>
    </div>

    <div style="height: 16px;"></div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">بيانات الاستبيان</h2>

            <div class="meta-grid">
                <div><strong>العنوان:</strong> {{ $survey->title }}</div>
                <div><strong>الوصف:</strong> {{ $survey->description ?: '—' }}</div>
                <div><strong>المستوى:</strong> {{ $scopeLabel }}</div>
                <div><strong>نوع الربط:</strong> {{ $isCourseSurvey ? 'مقرر' : 'عام' }}</div>
                <div><strong>الكلية:</strong> {{ $surveyFaculty }}</div>
                <div><strong>القسم:</strong> {{ $surveyDepartment }}</div>
                <div><strong>المقرر:</strong> {{ $surveyCourse }}</div>
                <div><strong>العام الدراسي:</strong> {{ $surveyAcademicYear }}</div>
                <div><strong>الفصل الدراسي:</strong> {{ $semesterName }}</div>
                <div><strong>الفرقة:</strong> {{ $survey->courseOffering?->level ?? $survey->level ?? '—' }}</div>
                <div><strong>القائم على التدريس:</strong> {{ $survey->courseOffering?->instructor_name ?? '—' }}</div>
                <div><strong>الهيئة المعاونة:</strong> {{ $survey->courseOffering?->assistant_name ?? '—' }}</div>
                <div><strong>الحالة:</strong> {{ $survey->is_active ? 'نشط' : 'غير نشط' }}</div>
                <div><strong>الحد الأقصى للردود:</strong> {{ $survey->expected_responses ?? 'غير محدد' }}</div>
                <div><strong>إغلاق تلقائي عند بلوغ الحد:</strong> {{ $survey->auto_close_on_limit ? 'نعم' : 'لا' }}</div>
                <div><strong>السماح بأكثر من رد من نفس الجهاز:</strong> {{ $survey->allow_multiple_submissions ? 'نعم' : 'لا' }}</div>
            </div>
        </div>
    </div>

    @foreach($survey->sections as $section)
        <div class="card section-card">
            <div class="card-body">
                <h2 class="section-title">{{ $section->title }}</h2>

                @foreach($section->questions as $question)
                    @php
                        $stats = $questionStats[$question->id] ?? null;
                    @endphp

                    <div class="question-box">
                        <div class="question-title">
                            {{ $question->display_order }}. {{ $question->question_text }}
                        </div>

                        @if($stats && in_array($stats['type'], ['scale', 'mcq']))
                            <div class="question-meta">
                                <span><strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}</span>
                                @if(!is_null($stats['average']))
                                    <span><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}</span>
                                @endif
                            </div>

                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>الاختيار</th>
                                            <th>عدد المرات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['distribution'] as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>
                                                <td>{{ $item['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($stats && $stats['type'] === 'text')
                            <div class="question-meta">
                                <span><strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}</span>
                            </div>

                            @forelse($stats['comments'] as $comment)
                                <div class="comment-box">{{ $comment }}</div>
                            @empty
                                <div class="empty-state small-empty-state">لا توجد تعليقات حتى الآن</div>
                            @endforelse
                        @else
                            <div class="empty-state small-empty-state">لا توجد بيانات متاحة لهذا السؤال</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @php
        $standaloneQuestions = $survey->questions->whereNull('survey_section_id');
    @endphp

    @if($standaloneQuestions->count())
        <div class="card section-card">
            <div class="card-body">
                <h2 class="section-title">أسئلة إضافية</h2>

                @foreach($standaloneQuestions as $question)
                    @php
                        $stats = $questionStats[$question->id] ?? null;
                    @endphp

                    <div class="question-box">
                        <div class="question-title">{{ $question->question_text }}</div>

                        @if($stats && $stats['type'] === 'text')
                            <div class="question-meta">
                                <span><strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}</span>
                            </div>

                            @forelse($stats['comments'] as $comment)
                                <div class="comment-box">{{ $comment }}</div>
                            @empty
                                <div class="empty-state small-empty-state">لا توجد تعليقات حتى الآن</div>
                            @endforelse
                        @elseif($stats && in_array($stats['type'], ['scale', 'mcq']))
                            <div class="question-meta">
                                <span><strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}</span>
                                @if(!is_null($stats['average']))
                                    <span><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}</span>
                                @endif
                            </div>

                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>الاختيار</th>
                                            <th>عدد المرات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['distribution'] as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>
                                                <td>{{ $item['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state small-empty-state">لا توجد بيانات متاحة لهذا السؤال</div>
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
        margin-top: 16px;
    }

    .question-box {
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        padding: 16px;
        margin-top: 16px;
        background: #fafbff;
    }

    .question-title {
        font-size: 16px;
        font-weight: 800;
        color: #28335f;
        margin-bottom: 12px;
        line-height: 1.8;
    }

    .question-meta {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-bottom: 12px;
        color: #4b5563;
    }

    .comment-box {
        background: #fff;
        border: 1px solid #e4e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        margin-top: 10px;
        line-height: 1.9;
    }

    .small-empty-state {
        padding: 16px;
        margin-top: 8px;
    }

    @media (max-width: 768px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush