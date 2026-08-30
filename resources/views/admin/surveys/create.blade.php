@extends('layouts.admin')

@php
    $pageTitle = 'إنشاء استبيان جديد';
    $pageSubtitle = 'إضافة استبيان جديد مع إمكانية استخدام قالب جاهز';
@endphp

@section('content')
    @php
        $courseOfferingsJson = $courseOfferings->map(function ($offering) {
            return [
                'id' => $offering->id,
                'course_id' => $offering->course_id,
                'course_name_ar' => $offering->course?->name_ar,
                'course_code' => $offering->course?->code,
                'academic_year' => $offering->academic_year,
                'semester' => $offering->semester,
                'level' => $offering->level,
                'instructor_name' => $offering->instructor_name,
                'assistant_name' => $offering->assistant_name,
            ];
        })->values();

        $templatesJson = $templates->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'scope_level' => $template->scope_level,
                'faculty_id' => $template->faculty_id,
                'department_id' => $template->department_id,
            ];
        })->values();

        $currentUser = auth()->user();
        $isUniversityAdmin = $currentUser->isUniversityAdmin();
        $isPresidencyAdmin = $currentUser->isPresidencyAdmin();
        $isFacultyAdmin = $currentUser->isFacultyAdmin();
        $isDepartmentAdmin = $currentUser->isDepartmentAdmin();

        $oldSurveyScope = old('survey_scope', 'general');
        $oldTemplateId = old('template_id');
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">رجوع إلى الاستبيانات</a>
        @unless($isPresidencyAdmin)

            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">

                قوالب الاستبيانات

            </a>

        @endunless
        @if($isDepartmentAdmin)
            <a href="{{ route('admin.surveys.bulk.create') }}" class="btn btn-secondary">إنشاء استبيانات جماعيًا</a>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.surveys.store') }}">
                @csrf

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">عنوان الاستبيان</label>
                        <input type="text" name="title" value="{{ old('title') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">نوع الاستبيان</label>

                        @if($isUniversityAdmin || $isPresidencyAdmin)
                            <input type="hidden" name="survey_scope" id="survey_scope" value="general">

                            <input type="text"
                        
                                value="{{ $isPresidencyAdmin ? 'استبيان المكتب الفني لرئيس الجامعة' : 'استبيان عام على مستوى الجامعة' }}"
                        
                                disabled>
                        
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                        
                                {{ $isPresidencyAdmin
                        
                                    ? 'هذا الحساب ينشئ استبيانات خاصة بالمكتب الفني لرئيس الجامعة فقط.'
                        
                                    : 'هذا الحساب ينشئ استبيانات عامة على مستوى الجامعة فقط.' }}
                        
                            </small>
                        @elseif($isFacultyAdmin)
                            <input type="hidden" name="survey_scope" id="survey_scope" value="general">
                            <input type="text" value="استبيان عام على مستوى الكلية" disabled>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                هذا الحساب ينشئ استبيانات عامة على مستوى الكلية فقط.
                            </small>
                        @else
                            <select name="survey_scope" id="survey_scope">
                                <option value="general" {{ $oldSurveyScope === 'general' ? 'selected' : '' }}>
                                    استبيان عام على مستوى القسم
                                </option>
                                <option value="course" {{ $oldSurveyScope === 'course' ? 'selected' : '' }}>
                                    استبيان خاص بمادة
                                </option>
                            </select>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                يمكنك إنشاء استبيان عام للقسم أو استبيان خاص بمادة من مواد القسم فقط.
                            </small>
                        @endif
                    </div>
                </div>

                <div class="grid-2">
                    @if($isUniversityAdmin || $isPresidencyAdmin)
                        <input type="hidden" name="scope_level" value="university">

                        <div class="form-group">
                    
                            <label class="form-label">نطاق الاستبيان</label>
                    
                            <input type="text"
                    
                                value="{{ $isPresidencyAdmin ? 'المكتب الفني لرئيس الجامعة' : 'على مستوى الجامعة' }}"
                    
                                disabled>
                    
                        </div>
                    
                        <div class="form-group">
                    
                            <label class="form-label">ملاحظات</label>
                    
                            <input type="text"
                    
                                value="{{ $isPresidencyAdmin
                    
                                        ? 'الاستبيان تابع للمكتب الفني لرئيس الجامعة'
                    
                                        : 'لا حاجة لتحديد كلية أو قسم أو مادة' }}"
                    
                                disabled>
                    
                        </div>
                    @elseif($isFacultyAdmin)
                        <input type="hidden" name="scope_level" value="faculty">
                        <input type="hidden" name="faculty_id" value="{{ $currentUser->faculty_id }}">

                        <div class="form-group">
                            <label class="form-label">نطاق الاستبيان</label>
                            <input type="text" value="على مستوى الكلية" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الكلية</label>
                            <input type="text" value="{{ $faculties->first()?->name_ar }}" disabled>
                        </div>
                    @else
                        <input type="hidden" name="scope_level" value="department">
                        <input type="hidden" name="faculty_id" value="{{ $currentUser->faculty_id }}">
                        <input type="hidden" name="department_id" value="{{ $currentUser->department_id }}">

                        <div class="form-group">
                            <label class="form-label">نطاق الاستبيان</label>
                            <input type="text" value="على مستوى القسم" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">القسم</label>
                            <input type="text" value="{{ $departments->first()?->name_ar }}" disabled>
                        </div>
                    @endif
                </div>

                @unless($isPresidencyAdmin)
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">استخدام قالب جاهز</label>
                        <select name="template_id" id="template_id">
                            <option value="">إنشاء استبيان بدون قالب</option>
                        </select>
                        <small style="display:block; margin-top:8px; color:#6b7280;">
                            سيتم عرض القوالب المناسبة تلقائيًا حسب نوع الاستبيان وصلاحيات الحساب.
                        </small>
                    </div>
                </div>
                    
                @endunless

                @if($isDepartmentAdmin)
                    <div id="course-offering-wrapper" style="display: {{ $oldSurveyScope === 'course' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label class="form-label">المادة المسجلة</label>
                            <select name="course_offering_id" id="course_offering_id">
                                <option value="">اختر المادة المسجلة</option>
                                @foreach($courseOfferings as $offering)
                                    <option value="{{ $offering->id }}" {{ old('course_offering_id') == $offering->id ? 'selected' : '' }}>
                                        {{ $offering->course?->name_ar }}
                                        {{ $offering->course?->code ? ' - ' . $offering->course?->code : '' }}
                                        | {{ $offering->academic_year }}
                                        | {{ $offering->semester === 'first' ? 'الفصل الدراسي الأول' : ($offering->semester === 'second' ? 'الفصل الدراسي الثاني' : 'الفصل الصيفي') }}
                                        | {{ $offering->level }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                اختر المادة المسجلة المطلوب إنشاء الاستبيان لها.
                            </small>
                        </div>

                        <div id="offering-preview" class="card" style="display:none; margin-bottom:16px; background:#f8f9ff; border-color:#d9def7;">
                            <div class="card-body" id="offering-preview-content"></div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">وصف الاستبيان</label>
                    <textarea name="description">{{ old('description') }}</textarea>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">عدد الردود المتوقع</label>
                        <input type="number" name="expected_responses" min="1" value="{{ old('expected_responses') }}" placeholder="مثال: 100">
                    </div>

                    <div class="checkbox-row" style="margin-top: 32px;">
                        <input type="checkbox" id="auto_close_on_limit" name="auto_close_on_limit" {{ old('auto_close_on_limit', true) ? 'checked' : '' }}>
                        <label for="auto_close_on_limit" style="margin:0;">إغلاق الاستبيان تلقائيًا عند الوصول إلى العدد المطلوب</label>
                    </div>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" style="margin:0;">تفعيل الاستبيان</label>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="allow_multiple_submissions" name="allow_multiple_submissions" {{ old('allow_multiple_submissions') ? 'checked' : '' }}>
                    <label for="allow_multiple_submissions" style="margin:0;">السماح بأكثر من رد من نفس الجهاز</label>
                </div>

                <div id="manual-builder-wrapper">
                    <hr style="margin:24px 0; border:none; border-top:1px solid #e4e8f0;">

                    <div class="page-actions" style="justify-content:space-between; align-items:center;">
                        <h2 class="section-title" style="margin:0;">المحاور والأسئلة</h2>
                        <button type="button" class="btn btn-primary" onclick="addSection()">إضافة محور</button>
                    </div>

                    <div id="sections-wrapper"></div>

                    <hr style="margin:24px 0; border:none; border-top:1px solid #e4e8f0;">

                    <div class="form-group">
                        <label class="form-label">سؤال التعليقات الإضافية</label>
                        <input type="text" name="comments_question" value="{{ old('comments_question', 'تعليقات أخرى') }}">
                    </div>
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ الاستبيان</button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">إلغاء</a>
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

.mcq-option {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mcq-option input {
    flex: 1;
}

.remove-option-btn {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #fecaca;
    border-radius: 10px;

    background: #fff1f2;
    color: #dc2626;

    font-size: 22px;
    font-weight: 700;

    cursor: pointer;
}

.remove-option-btn:hover {
    background: #fee2e2;
}

.add-option-btn {
    margin-top: 8px;
}
</style>
@endpush

@push('scripts')
<script>
    let sectionIndex = 0;
    const questionCounters = {};

    function addSection() {
        const wrapper = document.getElementById('sections-wrapper');
        const currentSectionIndex = sectionIndex;

        questionCounters[currentSectionIndex] = 0;

        const html = `
            <div class="section-box" id="section-${currentSectionIndex}">
                <div class="section-title-inline">محور جديد</div>

                <div class="form-group">
                    <label class="form-label">عنوان المحور</label>
                    <input
                        type="text"
                        name="sections[${currentSectionIndex}][title]"
                    >
                </div>

                <div id="questions-wrapper-${currentSectionIndex}"></div>

                <div class="page-actions">
                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="addQuestion(${currentSectionIndex})"
                    >
                        إضافة سؤال
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="removeElement('section-${currentSectionIndex}')"
                    >
                        حذف المحور
                    </button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);

        sectionIndex++;
    }

    function addQuestion(sectionIdx) {
        const wrapper = document.getElementById(
            `questions-wrapper-${sectionIdx}`
        );

        if (!wrapper) {
            return;
        }

        if (typeof questionCounters[sectionIdx] === 'undefined') {
            questionCounters[sectionIdx] = 0;
        }

        const questionIdx = questionCounters[sectionIdx]++;

        const html = `
            <div
                class="question-box"
                id="section-${sectionIdx}-question-${questionIdx}"
            >
                <div class="form-group">
                    <label class="form-label">نص السؤال</label>

                    <textarea
                        name="sections[${sectionIdx}][questions][${questionIdx}][question_text]"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">نوع السؤال</label>

                    <select
                        name="sections[${sectionIdx}][questions][${questionIdx}][type]"
                        onchange="toggleOptions(this, ${sectionIdx}, ${questionIdx})"
                    >
                        <option value="scale">تقييم 1-5</option>
                        <option value="mcq">اختيار من متعدد</option>
                        <option value="text">نص مفتوح</option>
                        <option value="date">تاريخ</option>
                    </select>
                </div>

                <div class="checkbox-row">
                    <input
                        type="checkbox"
                        id="required-${sectionIdx}-${questionIdx}"
                        name="sections[${sectionIdx}][questions][${questionIdx}][is_required]"
                        checked
                    >

                    <label
                        for="required-${sectionIdx}-${questionIdx}"
                        style="margin:0;"
                    >
                        سؤال إجباري
                    </label>
                </div>

                <div
                    class="form-group options-box"
                    id="options-box-${sectionIdx}-${questionIdx}"
                >
                    <label class="form-label">الخيارات</label>

                    <div
                        class="options-list"
                        id="options-list-${sectionIdx}-${questionIdx}"
                    >
                    </div>

                    <button
                        type="button"
                        class="btn btn-secondary add-option-btn"
                        id="add-option-btn-${sectionIdx}-${questionIdx}"
                        style="display:none; margin-top:10px;"
                        onclick="addOption(${sectionIdx}, ${questionIdx})"
                    >
                        + إضافة اختيار
                    </button>
                </div>

                <div class="page-actions">
                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="removeElement('section-${sectionIdx}-question-${questionIdx}')"
                    >
                        حذف السؤال
                    </button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);

        setScaleOptions(sectionIdx, questionIdx);
    }

    function toggleOptions(selectEl, sectionIdx, questionIdx) {
        const type = selectEl.value;

        const box = document.getElementById(
            `options-box-${sectionIdx}-${questionIdx}`
        );

        const list = document.getElementById(
            `options-list-${sectionIdx}-${questionIdx}`
        );

        const addButton = document.getElementById(
            `add-option-btn-${sectionIdx}-${questionIdx}`
        );

        if (!box || !list || !addButton) {
            return;
        }

        /*
         * Text + Date
         * لا يحتاجان اختيارات.
         */
        if (type === 'text' || type === 'date') {
            box.style.display = 'none';
            list.innerHTML = '';
            return;
        }

        box.style.display = 'block';

        /*
         * Scale
         * يظل ثابتًا على خمس درجات.
         */
        if (type === 'scale') {
            addButton.style.display = 'none';
            setScaleOptions(sectionIdx, questionIdx);
            return;
        }

        /*
         * Multiple Choice
         */
        if (type === 'mcq') {
            list.innerHTML = '';
            addButton.style.display = 'inline-flex';

            addOption(sectionIdx, questionIdx, 'اختيار 1');
            addOption(sectionIdx, questionIdx, 'اختيار 2');
        }
    }

    function setScaleOptions(sectionIdx, questionIdx) {
        const list = document.getElementById(
            `options-list-${sectionIdx}-${questionIdx}`
        );

        const addButton = document.getElementById(
            `add-option-btn-${sectionIdx}-${questionIdx}`
        );

        if (!list) {
            return;
        }

        if (addButton) {
            addButton.style.display = 'none';
        }

        const scaleOptions = [
            'غير موافق بشدة',
            'غير موافق',
            'محايد',
            'أوافق',
            'أوافق بشدة'
        ];

        list.innerHTML = '';

        scaleOptions.forEach(optionText => {
            list.insertAdjacentHTML(
                'beforeend',
                `
                    <div class="option-input">
                        <input
                            type="text"
                            name="sections[${sectionIdx}][questions][${questionIdx}][options][]"
                            value="${optionText}"
                        >
                    </div>
                `
            );
        });
    }

    function addOption(sectionIdx, questionIdx, value = '') {
        const list = document.getElementById(
            `options-list-${sectionIdx}-${questionIdx}`
        );

        if (!list) {
            return;
        }

        const html = `
            <div class="option-input mcq-option">
                <input
                    type="text"
                    name="sections[${sectionIdx}][questions][${questionIdx}][options][]"
                    value="${value}"
                    placeholder="اكتب الاختيار"
                >

                <button
                    type="button"
                    class="remove-option-btn"
                    onclick="removeOption(this)"
                    title="حذف الاختيار"
                >
                    ×
                </button>
            </div>
        `;

        list.insertAdjacentHTML('beforeend', html);
    }

    function removeOption(button) {
        const optionRow = button.closest('.option-input');

        if (!optionRow) {
            return;
        }

        const list = optionRow.parentElement;
        const optionsCount = list.querySelectorAll('.option-input').length;

        /*
         * نحافظ على اختيارين على الأقل في MCQ.
         */
        if (optionsCount <= 2) {
            alert('يجب أن يحتوي سؤال الاختيار من متعدد على اختيارين على الأقل.');
            return;
        }

        optionRow.remove();
    }

    function removeElement(id) {
        const el = document.getElementById(id);

        if (el) {
            el.remove();
        }
    }
</script>

<script>
    const offeringsData = @json($courseOfferingsJson);
    const templatesData = @json($templatesJson);
    const oldTemplateId = @json($oldTemplateId);
    const oldSurveyScope = @json($oldSurveyScope);
    const isDepartmentAdmin = {{ $isDepartmentAdmin ? 'true' : 'false' }};

    const semesterLabels = {
        first: 'الفصل الدراسي الأول',
        second: 'الفصل الدراسي الثاني',
        summer: 'الفصل الصيفي',
    };

    const surveyScopeSelect = document.getElementById('survey_scope');
    const courseOfferingWrapper = document.getElementById('course-offering-wrapper');
    const offeringSelect = document.getElementById('course_offering_id');
    const offeringPreview = document.getElementById('offering-preview');
    const offeringPreviewContent = document.getElementById('offering-preview-content');

    const scopeLevelInput = document.querySelector('input[name="scope_level"]');
    const templateSelect = document.getElementById('template_id');
    const manualBuilderWrapper = document.getElementById('manual-builder-wrapper');

    function getCurrentTemplateScope() {
        if (isDepartmentAdmin && surveyScopeSelect && surveyScopeSelect.value === 'course') {
            return 'course';
        }

        return scopeLevelInput ? scopeLevelInput.value : 'department';
    }

    function showOfferingPreview() {
        if (!offeringPreview || !offeringPreviewContent || !offeringSelect) return;

        offeringPreview.style.display = 'none';
        offeringPreviewContent.innerHTML = '';

        const selectedOfferingId = offeringSelect.value;
        if (!selectedOfferingId) return;

        const selectedOffering = offeringsData.find(
            offering => String(offering.id) === String(selectedOfferingId)
        );

        if (!selectedOffering) return;

        offeringPreviewContent.innerHTML = `
            <strong>بيانات المادة المسجلة:</strong><br>
            المقرر: ${selectedOffering.course_name_ar ?? '-'} ${selectedOffering.course_code ? ' - ' + selectedOffering.course_code : ''}<br>
            العام الدراسي: ${selectedOffering.academic_year ?? '-'}<br>
            الفصل الدراسي: ${semesterLabels[selectedOffering.semester] ?? selectedOffering.semester ?? '-'}<br>
            الفرقة: ${selectedOffering.level ?? '-'}<br>
            القائم على التدريس: ${selectedOffering.instructor_name ?? '-'}<br>
            الهيئة المعاونة: ${selectedOffering.assistant_name ?? '-'}
        `;
        offeringPreview.style.display = 'block';
    }

    function toggleSurveyScope() {
        if (!surveyScopeSelect || !courseOfferingWrapper) {
            populateTemplates();
            return;
        }

        if (surveyScopeSelect.value === 'course') {
            courseOfferingWrapper.style.display = 'block';
        } else {
            courseOfferingWrapper.style.display = 'none';
            if (offeringSelect) offeringSelect.value = '';
            if (offeringPreview) offeringPreview.style.display = 'none';
            if (offeringPreviewContent) offeringPreviewContent.innerHTML = '';
        }

        populateTemplates();
    }

    function populateTemplates() {
        if (!templateSelect) return;

        const currentScope = getCurrentTemplateScope();
        const currentValue = templateSelect.value || oldTemplateId || '';

        templateSelect.innerHTML = '<option value="">إنشاء استبيان بدون قالب</option>';

        const filteredTemplates = templatesData.filter(template => template.scope_level === currentScope);

        filteredTemplates.forEach(template => {
            const option = document.createElement('option');
            option.value = template.id;
            option.textContent = template.name;

            if (String(currentValue) === String(template.id)) {
                option.selected = true;
            }

            templateSelect.appendChild(option);
        });

        toggleTemplateMode();
    }

    function toggleTemplateMode() {
        if (!templateSelect || !manualBuilderWrapper) return;

        if (templateSelect.value) {
            manualBuilderWrapper.style.display = 'none';
        } else {
            manualBuilderWrapper.style.display = 'block';

            if (!document.getElementById('sections-wrapper').children.length) {
                addSection();
            }
        }
    }

    surveyScopeSelect?.addEventListener('change', toggleSurveyScope);
    offeringSelect?.addEventListener('change', showOfferingPreview);
    templateSelect?.addEventListener('change', toggleTemplateMode);

    toggleSurveyScope();
    populateTemplates();
    showOfferingPreview();

    if (!templateSelect.value && !document.getElementById('sections-wrapper').children.length) {
        addSection();
    }
</script>
@endpush