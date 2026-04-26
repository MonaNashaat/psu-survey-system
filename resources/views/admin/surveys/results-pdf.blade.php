<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نتائج الاستبيان</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            line-height: 1.8;
            color: #222;
        }

        h1, h2, h3 {
            margin: 0 0 8px 0;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 14px;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 13px;
            margin-top: 12px;
        }

        .meta {
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #bbb;
            background: #fafafa;
        }

        .meta-item {
            margin-bottom: 4px;
        }

        .section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #ddd;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #bbb;
        }

        .question-block {
            margin-top: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }

        .stats {
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }

        .comment {
            margin: 6px 0;
            padding: 6px;
            border: 1px solid #ccc;
            background: #fafafa;
        }

        .empty {
            padding: 6px;
            border: 1px solid #ddd;
            background: #fcfcfc;
            color: #666;
        }
    </style>
</head>
<body>
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

    <h1>نتائج الاستبيان</h1>

    <div class="meta">
        <div class="meta-item"><strong>العنوان:</strong> {{ $survey->title }}</div>
        <div class="meta-item"><strong>الوصف:</strong> {{ $survey->description ?: '—' }}</div>
        <div class="meta-item"><strong>نوع الاستبيان:</strong> {{ $isCourseSurvey ? 'استبيان مرتبط بمادة' : 'استبيان عام' }}</div>
        <div class="meta-item"><strong>عدد الردود:</strong> {{ $responsesCount }}</div>

        @if($isCourseSurvey)
            <div class="meta-item"><strong>الكلية:</strong> {{ $survey->courseOffering?->course?->department?->faculty?->name_ar ?? '-' }}</div>
            <div class="meta-item"><strong>القسم:</strong> {{ $survey->courseOffering?->course?->department?->name_ar ?? ($survey->department_name ?? '-') }}</div>
            <div class="meta-item"><strong>المقرر:</strong> {{ $survey->courseOffering?->course?->name_ar ?? ($survey->course_title ?? '-') }}</div>
            <div class="meta-item"><strong>كود المقرر:</strong> {{ $survey->courseOffering?->course?->code ?? '-' }}</div>
            <div class="meta-item"><strong>الفصل الدراسي:</strong> {{ $semesterName }}</div>
            <div class="meta-item"><strong>الفرقة:</strong> {{ $survey->courseOffering?->level ?? ($survey->level ?? '-') }}</div>
            <div class="meta-item"><strong>العام الدراسي:</strong> {{ $survey->courseOffering?->academic_year ?? ($survey->academic_year ?? '-') }}</div>
            <div class="meta-item"><strong>القائم على التدريس:</strong> {{ $survey->courseOffering?->instructor_name ?? '-' }}</div>
            <div class="meta-item"><strong>الهيئة المعاونة:</strong> {{ $survey->courseOffering?->assistant_name ?? '-' }}</div>
        @endif
    </div>

    @foreach($survey->sections as $section)
        <div class="section">
            <div class="section-title">{{ $section->title }}</div>

            @foreach($section->questions as $question)
                @php
                    $stats = $questionStats[$question->id] ?? null;
                @endphp

                <div class="question-block">
                    <h3>{{ $question->display_order }}. {{ $question->question_text }}</h3>

                    @if($stats && in_array($stats['type'], ['scale', 'mcq']))
                        <div class="stats">
                            <strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}
                            @if(!is_null($stats['average']))
                                <br><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}
                            @endif
                        </div>

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
                    @elseif($stats && $stats['type'] === 'text')
                        <div class="stats">
                            <strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}
                        </div>

                        @forelse($stats['comments'] as $comment)
                            <div class="comment">{{ $comment }}</div>
                        @empty
                            <div class="empty">لا توجد تعليقات.</div>
                        @endforelse
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach

    @if($standaloneQuestions->count())
        <div class="section">
            <div class="section-title">أسئلة إضافية</div>

            @foreach($standaloneQuestions as $question)
                @php
                    $stats = $questionStats[$question->id] ?? null;
                @endphp

                <div class="question-block">
                    <h3>{{ $question->question_text }}</h3>

                    @if($stats && $stats['type'] === 'text')
                        <div class="stats">
                            <strong>عدد التعليقات:</strong> {{ $stats['total_answers'] }}
                        </div>

                        @forelse($stats['comments'] as $comment)
                            <div class="comment">{{ $comment }}</div>
                        @empty
                            <div class="empty">لا توجد تعليقات.</div>
                        @endforelse
                    @elseif($stats && in_array($stats['type'], ['scale', 'mcq']))
                        <div class="stats">
                            <strong>عدد الإجابات:</strong> {{ $stats['total_answers'] }}
                            @if(!is_null($stats['average']))
                                <br><strong>المتوسط:</strong> {{ number_format($stats['average'], 2) }}
                            @endif
                        </div>

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
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>