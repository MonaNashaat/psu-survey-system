@extends('layouts.admin')

@php
    $pageTitle = 'تعديل قالب';
    $pageSubtitle = 'تعديل قالب استبيان موجود وإدارة محاوره وأسئلته';
@endphp

@section('content')
    @php
        $standaloneQuestion = $template->questions->firstWhere('survey_template_section_id', null);

        $currentUser = auth()->user();
        $isUniversityAdmin = $currentUser->isUniversityAdmin();
        $isFacultyAdmin = $currentUser->isFacultyAdmin();
        $isDepartmentAdmin = $currentUser->isDepartmentAdmin();

        $scopeLevel = old('scope_level', $template->scope_level ?? ($isDepartmentAdmin ? 'department' : 'faculty'));

        $departmentsJson = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name_ar' => $department->name_ar,
                'faculty_id' => $department->faculty_id,
            ];
        })->values();
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">الرجوع إلى القوالب</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.templates.update', $template->id) }}">
                @csrf
                @method('PUT')

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">اسم القالب</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}">
                    </div>

                    <div class="checkbox-row" style="margin-top: 32px;">
                        <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0;">تفعيل القالب</label>
                    </div>
                </div>

                <div class="grid-2">
                    @if($isUniversityAdmin)
                        <input type="hidden" name="scope_level" id="scope_level" value="university">

                        <div class="form-group">
                            <label class="form-label">نوع القالب</label>
                            <input type="text" value="قالب على مستوى الجامعة" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ملاحظة</label>
                            <input type="text" value="أدمن الجامعة يعدّل قوالب الجامعة فقط" disabled>
                        </div>
                    @elseif($isFacultyAdmin)
                        <input type="hidden" name="faculty_id" id="faculty_id" value="{{ $currentUser->faculty_id }}">

                        <div class="form-group">
                            <label class="form-label">نوع القالب</label>
                            <select name="scope_level" id="scope_level">
                                <option value="faculty" {{ $scopeLevel === 'faculty' ? 'selected' : '' }}>قالب استبيان كلية</option>
                                <option value="course" {{ $scopeLevel === 'course' ? 'selected' : '' }}>قالب استبيان مادة</option>
                            </select>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                قالب استبيان المادة يكون خاصًا بالكلية ويستخدمه أدمن القسم عند إنشاء استبيانات المواد.
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الكلية</label>
                            <input type="text" value="{{ $faculties->first()?->name_ar }}" disabled>
                        </div>
                    @else
                        <input type="hidden" name="scope_level" id="scope_level" value="department">
                        <input type="hidden" name="faculty_id" id="faculty_id" value="{{ $currentUser->faculty_id }}">
                        <input type="hidden" name="department_id" id="department_id" value="{{ $currentUser->department_id }}">

                        <div class="form-group">
                            <label class="form-label">نوع القالب</label>
                            <input type="text" value="قالب استبيان قسم" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">القسم</label>
                            <input type="text" value="{{ $departments->first()?->name_ar }}" disabled>
                        </div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">وصف القالب</label>
                    <textarea name="description">{{ old('description', $template->description) }}</textarea>
                </div>

                <hr style="margin:24px 0; border:none; border-top:1px solid #e4e8f0;">

                <div class="page-actions" style="justify-content:space-between; align-items:center;">
                    <h2 class="section-title" style="margin:0;">المحاور والأسئلة</h2>
                    <button type="button" class="btn btn-primary" onclick="addSection()">إضافة محور</button>
                </div>

                <div id="sections-wrapper"></div>

                <hr style="margin:24px 0; border:none; border-top:1px solid #e4e8f0;">

                <div class="form-group">
                    <label class="form-label">سؤال التعليقات الإضافية</label>
                    <input type="text" name="comments_question" value="{{ old('comments_question', $standaloneQuestion?->question_text ?? 'تعليقات أخرى') }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .section-box,
    .question-box {
        border: 1px solid #e4e8f0;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fafbff;
    }

    .section-title-inline {
        margin-bottom: 12px;
        font-size: 18px;
        font-weight: 800;
        color: #28335f;
    }

    .option-input {
        margin-bottom: 8px;
    }
</style>
@endpush

@push('scripts')
<script>
    let sectionIndex = 0;

    function removeElement(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function addSection(title = '', questions = []) {
        const wrapper = document.getElementById('sections-wrapper');
        const currentSectionIndex = sectionIndex;

        const html = `
            <div class="section-box" id="section-${currentSectionIndex}">
                <div class="section-title-inline">محور</div>

                <div class="form-group">
                    <label class="form-label">عنوان المحور</label>
                    <input type="text" name="sections[${currentSectionIndex}][title]" value="${escapeHtml(title)}">
                </div>

                <div id="questions-wrapper-${currentSectionIndex}"></div>

                <div class="page-actions">
                    <button type="button" class="btn btn-primary" onclick="addQuestion(${currentSectionIndex})">إضافة سؤال</button>
                    <button type="button" class="btn btn-danger" onclick="removeElement('section-${currentSectionIndex}')">حذف المحور</button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);

        questions.forEach(question => {
            addQuestion(
                currentSectionIndex,
                question.question_text,
                question.type,
                question.is_required,
                question.options || []
            );
        });

        sectionIndex++;
    }

    function addQuestion(sectionIdx, questionText = '', questionType = 'scale', isRequired = true, options = []) {
        const wrapper = document.getElementById(`questions-wrapper-${sectionIdx}`);
        const questionCount = wrapper.querySelectorAll('.question-box').length;

        let optionsHtml = '';

        if (options.length === 0 && (questionType === 'scale' || questionType === 'mcq')) {
            options = questionType === 'scale'
                ? ['غير موافق بشدة', 'غير موافق', 'محايد', 'أوافق', 'أوافق بشدة']
                : ['', ''];
        }

        options.forEach(option => {
            optionsHtml += `
                <div class="option-input">
                    <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="${escapeHtml(option)}">
                </div>
            `;
        });

        const html = `
            <div class="question-box" id="section-${sectionIdx}-question-${questionCount}">
                <div class="form-group">
                    <label class="form-label">نص السؤال</label>
                    <textarea name="sections[${sectionIdx}][questions][${questionCount}][question_text]">${escapeHtml(questionText)}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">نوع السؤال</label>
                    <select name="sections[${sectionIdx}][questions][${questionCount}][type]" onchange="toggleOptions(this, ${sectionIdx}, ${questionCount})">
                        <option value="scale" ${questionType === 'scale' ? 'selected' : ''}>تقييم 1-5</option>
                        <option value="mcq" ${questionType === 'mcq' ? 'selected' : ''}>اختيار من متعدد</option>
                        <option value="text" ${questionType === 'text' ? 'selected' : ''}>نص مفتوح</option>
                    </select>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="required-${sectionIdx}-${questionCount}" name="sections[${sectionIdx}][questions][${questionCount}][is_required]" ${isRequired ? 'checked' : ''}>
                    <label for="required-${sectionIdx}-${questionCount}" style="margin:0;">سؤال إجباري</label>
                </div>

                <div class="form-group options-box" id="options-box-${sectionIdx}-${questionCount}" style="${questionType === 'text' ? 'display:none;' : ''}">
                    <label class="form-label">الخيارات</label>
                    ${optionsHtml}
                    <button type="button" class="btn btn-secondary" onclick="addOption(${sectionIdx}, ${questionCount})">إضافة اختيار</button>
                </div>

                <div class="page-actions">
                    <button type="button" class="btn btn-danger" onclick="removeElement('section-${sectionIdx}-question-${questionCount}')">حذف السؤال</button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);
    }

    function addOption(sectionIdx, questionIdx) {
        const box = document.getElementById(`options-box-${sectionIdx}-${questionIdx}`);
        const btn = box.querySelector('button');
        const div = document.createElement('div');

        div.className = 'option-input';
        div.innerHTML = `<input type="text" name="sections[${sectionIdx}][questions][${questionIdx}][options][]" value="">`;

        box.insertBefore(div, btn);
    }

    function toggleOptions(selectEl, sectionIdx, questionIdx) {
        const box = document.getElementById(`options-box-${sectionIdx}-${questionIdx}`);
        box.style.display = selectEl.value === 'text' ? 'none' : 'block';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    const existingSections = [
        @foreach($template->sections as $section)
            {
                title: @json($section->title),
                questions: [
                    @foreach($section->questions as $question)
                        {
                            question_text: @json($question->question_text),
                            type: @json($question->type),
                            is_required: {{ $question->is_required ? 'true' : 'false' }},
                            options: [
                                @foreach($question->options as $option)
                                    @json($option->option_text),
                                @endforeach
                            ]
                        },
                    @endforeach
                ]
            },
        @endforeach
    ];

    if (existingSections.length) {
        existingSections.forEach(section => addSection(section.title, section.questions));
    } else {
        addSection();
    }
</script>
@endpush