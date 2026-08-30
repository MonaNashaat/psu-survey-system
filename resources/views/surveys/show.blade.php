@extends('layouts.guest-survey')

@php
    $pageTitle = $survey->title;
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

@push('styles')
<style>
    .survey-header {
        padding: 28px;
        margin-bottom: 18px;
    }

    .survey-badge {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        border-radius: 999px;
        background: #eef1ff;
        color: #28335f;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .survey-title {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 800;
        color: #28335f;
        line-height: 1.4;
    }

    .survey-description {
        margin: 0;
        color: #6b7280;
        line-height: 1.9;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 18px;
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid #e4e8f0;
    }

    .meta-item {
        background: #fafbff;
        border: 1px solid #e4e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 14px;
        line-height: 1.8;
    }

    .meta-item strong {
        color: #28335f;
    }

    .section-box {
        margin-bottom: 18px;
        overflow: hidden;
    }

    .section-title {
        background: linear-gradient(135deg, #28335f 0%, #3a4a84 100%);
        color: #fff;
        padding: 16px 20px;
        font-weight: 800;
        font-size: 18px;
    }

    .question {
        padding: 18px 20px;
        border-top: 1px solid #eef1f5;
    }

    .question:first-child {
        border-top: 0;
    }

    .question-text {
        margin-bottom: 14px;
        font-weight: 700;
        color: #222;
        line-height: 1.9;
    }

    .required-star {
        color: #d32f2f;
        margin-right: 4px;
    }

    .options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .option-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fd;
        border: 1px solid #e4e8f0;
        padding: 10px 14px;
        border-radius: 14px;
        cursor: pointer;
        transition: 0.2s ease;
        font-size: 14px;
    }

    .option-label:hover {
        border-color: #bfc8ea;
        background: #f3f5ff;
    }

    textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #d5dbe7;
        border-radius: 14px;
        padding: 14px;
        resize: vertical;
        font-family: 'Alexandria', sans-serif;
        font-size: 14px;
        color: #1f2a44;
    }

    .standalone-box {
        padding: 22px;
        margin-top: 18px;
    }

    .standalone-title {
        margin: 0 0 14px;
        font-size: 20px;
        color: #28335f;
    }

    .error {
        color: #c62828;
        margin-top: 8px;
        font-size: 13px;
        line-height: 1.7;
    }

    .submit-area {
        text-align: center;
        margin-top: 24px;
    }

    .submit-btn {
        background: linear-gradient(135deg, #28335f 0%, #3f5297 100%);
        color: white;
        border: 0;
        padding: 14px 34px;
        border-radius: 14px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 800;
        font-family: 'Alexandria', sans-serif;
    }
    .date-input {
    width: 100%;
    max-width: 360px;
    border: 1px solid #d5dbe7;
    border-radius: 14px;
    padding: 13px 14px;
    font-family: 'Alexandria', sans-serif;
    font-size: 14px;
    color: #1f2a44;
    background: #fff;
    direction: rtl;
}

.date-input:focus {
    outline: none;
    border-color: #8d9bd0;
    box-shadow: 0 0 0 3px rgba(40, 51, 95, 0.08);
}
    @media (max-width: 800px) {
        .meta-grid {
            grid-template-columns: 1fr;
        }

        .survey-title {
            font-size: 24px;
        }

        .options {
            flex-direction: column;
        }

        .survey-header,
        .standalone-box,
        .question,
        .section-title {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>
@endpush

@if($isCourseSurvey)
<div class="guest-card survey-header">
    
    <p class="survey-description">{{ config('app.name', 'منصة الاستبيانات') }}</p>




    <div class="meta-grid">

            <div class="meta-item"><strong>الكلية:</strong> {{ $survey->courseOffering?->course?->department?->faculty?->name_ar ?? '-' }}</div>
            <div class="meta-item"><strong>القسم:</strong> {{ $survey->courseOffering?->course?->department?->name_ar ?? '-' }}</div>
            <div class="meta-item"><strong>اسم المقرر:</strong> {{ $survey->courseOffering?->course?->name_ar ?? ($survey->course_title ?? '-') }}</div>
            <div class="meta-item"><strong>كود المقرر:</strong> {{ $survey->courseOffering?->course?->code ?? '-' }}</div>
            <div class="meta-item"><strong>العام الدراسي:</strong> {{ $survey->courseOffering?->academic_year ?? ($survey->academic_year ?? '-') }}</div>
            <div class="meta-item"><strong>الفصل الدراسي:</strong> {{ $semesterName }}</div>
            <div class="meta-item"><strong>الفرقة:</strong> {{ $survey->courseOffering?->level ?? ($survey->level ?? '-') }}</div>
            <div class="meta-item"><strong>القائم على التدريس:</strong> {{ $survey->courseOffering?->instructor_name ?? '-' }}</div>
            <div class="meta-item"><strong>الهيئة المعاونة:</strong> {{ $survey->courseOffering?->assistant_name ?? '-' }}</div>
       
    </div>
</div>
@endif

@if(session('duplicate_error'))
    <div class="alert alert-danger">
        {{ session('duplicate_error') }}
    </div>
@endif

@if(!empty($alreadySubmittedByCookie) && !$survey->allow_multiple_submissions)
    <div class="alert alert-warning">
        يبدو أنه تم إرسال هذا الاستبيان من هذا الجهاز من قبل.
    </div>
@endif

<div class="guest-card" style="padding: 0 0 24px 0;">
    <form method="POST" action="{{ route('surveys.submit', $survey->id) }}">
        @csrf

        @foreach($survey->sections as $section)
            <div class="section-box">
                <div class="section-title">{{ $section->title }}</div>

                @foreach($section->questions as $question)
                    <div class="question">
                        <div class="question-text">
                            {{ $question->display_order }}. {{ $question->question_text }}
                            @if($question->is_required)
                                <span class="required-star">*</span>
                            @endif
                        </div>

                        @if($question->type === 'mcq' || $question->type === 'scale')
                            <div class="options">
                                @foreach($question->options as $option)
                                    <label class="option-label">
                                        <input
                                            type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $option->id }}"
                                            {{ old('answers.' . $question->id) == $option->id ? 'checked' : '' }}
                                        >
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($question->type === 'text')
                            <textarea name="answers[{{ $question->id }}]">{{ old('answers.' . $question->id) }}</textarea>

                        @elseif($question->type === 'date')
                            <input
                                type="date"
                                name="answers[{{ $question->id }}]"
                                value="{{ old('answers.' . $question->id) }}"
                                class="date-input"
                            >
                        @endif

                        @error('answers.' . $question->id)
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>
        @endforeach

        @if($standaloneQuestions->count())
            <div class="standalone-box">
                <h3 class="standalone-title">تعليقات إضافية</h3>

                @foreach($standaloneQuestions as $question)
                <div class="question" style="padding: 0 0 18px 0; border-top:0;">
                    <div class="question-text">
                        {{ $question->question_text }}
                
                        @if($question->is_required)
                            <span class="required-star">*</span>
                        @endif
                    </div>
                
                    @if($question->type === 'mcq' || $question->type === 'scale')
                
                        <div class="options">
                            @foreach($question->options as $option)
                                <label class="option-label">
                                    <input
                                        type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $option->id }}"
                                        {{ old('answers.' . $question->id) == $option->id ? 'checked' : '' }}
                                    >
                                    <span>{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                
                    @elseif($question->type === 'text')
                
                        <textarea name="answers[{{ $question->id }}]">{{ old('answers.' . $question->id) }}</textarea>
                
                    @elseif($question->type === 'date')
                
                        <input
                            type="date"
                            name="answers[{{ $question->id }}]"
                            value="{{ old('answers.' . $question->id) }}"
                            class="date-input"
                        >
                
                    @endif
                
                    @error('answers.' . $question->id)
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                @endforeach
            </div>
        @endif

        <div class="submit-area">
            <button class="submit-btn" type="submit">إرسال</button>
        </div>
    </form>
</div>
@endsection