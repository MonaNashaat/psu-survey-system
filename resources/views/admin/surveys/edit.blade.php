@extends('layouts.admin')

@php
    $pageTitle = 'تعديل الاستبيان';
    $pageSubtitle = 'تعديل بيانات الاستبيان والمحاور والأسئلة';
@endphp

@section('content')
    @php
        $standaloneQuestion = $survey->questions->firstWhere('survey_section_id', null);

        $selectedOffering = $survey->courseOffering;
        $selectedCourse = $selectedOffering?->course;
        $selectedDepartment = $selectedCourse?->department;
        $selectedFaculty = $selectedDepartment?->faculty;

        $currentUser = auth()->user();
        $isUniversityAdmin = $currentUser->isUniversityAdmin();
        $isFacultyAdmin = $currentUser->isFacultyAdmin();
        $isDepartmentAdmin = $currentUser->isDepartmentAdmin();

        $surveyScope = old('survey_scope', $survey->course_offering_id ? 'course' : 'general');
        $scopeLevel = $isUniversityAdmin ? 'university' : ($isFacultyAdmin ? 'faculty' : 'department');

        $departmentsJson = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name_ar' => $department->name_ar,
                'faculty_id' => $department->faculty_id,
            ];
        })->values();

        $coursesJson = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name_ar' => $course->name_ar,
                'code' => $course->code,
                'department_id' => $course->department_id,
            ];
        })->values();

        $offeringsJson = $courseOfferings->map(function ($offering) {
            return [
                'id' => $offering->id,
                'course_id' => $offering->course_id,
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
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">عرض الاستبيان</a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">الرجوع إلى الاستبيانات</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.surveys.update', $survey->id) }}">
                @csrf
                @method('PUT')

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">عنوان الاستبيان</label>
                        <input type="text" name="title" value="{{ old('title', $survey->title) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">نوع الاستبيان</label>

                        @if($isUniversityAdmin)
                            <input type="hidden" name="survey_scope" id="survey_scope" value="general">
                            <input type="text" value="استبيان عام على مستوى الجامعة" disabled>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                أدمن الجامعة يعدّل استبيانات الجامعة فقط.
                            </small>
                        @elseif($isFacultyAdmin)
                            <input type="hidden" name="survey_scope" id="survey_scope" value="general">
                            <input type="text" value="استبيان عام على مستوى الكلية" disabled>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                أدمن الكلية يعدّل استبيانات الكلية فقط، ولا يمكنه تعديل استبيان مادة أو استبيان قسم.
                            </small>
                        @else
                            <select name="survey_scope" id="survey_scope">
                                <option value="general" {{ $surveyScope === 'general' ? 'selected' : '' }}>استبيان عام على مستوى القسم</option>
                                <option value="course" {{ $surveyScope === 'course' ? 'selected' : '' }}>استبيان خاص بمادة داخل القسم</option>
                            </select>
                            <small style="display:block; margin-top:8px; color:#6b7280;">
                                يمكنك تعديل استبيان القسم أو استبيان مادة من نفس القسم فقط.
                            </small>
                        @endif
                    </div>
                </div>

                <div class="grid-2">
                    @if($isUniversityAdmin)
                        <input type="hidden" name="scope_level" id="scope_level" value="university">

                        <div class="form-group">
                            <label class="form-label">نطاق الاستبيان</label>
                            <input type="text" value="استبيان على مستوى الجامعة" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ملاحظة</label>
                            <input type="text" value="لا يمكن لأدمن الجامعة تعديل استبيان كلية أو قسم أو مادة" disabled>
                        </div>
                    @elseif($isFacultyAdmin)
                        <input type="hidden" name="scope_level" id="scope_level" value="faculty">
                        <input type="hidden" name="faculty_id" id="survey_faculty_id" value="{{ $currentUser->faculty_id }}">

                        <div class="form-group">
                            <label class="form-label">نطاق الاستبيان</label>
                            <input type="text" value="استبيان على مستوى الكلية" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">الكلية</label>
                            <input type="text" value="{{ $faculties->first()?->name_ar }}" disabled>
                        </div>
                    @else
                        <input type="hidden" name="scope_level" id="scope_level" value="department">
                        <input type="hidden" name="faculty_id" id="survey_faculty_id" value="{{ $currentUser->faculty_id }}">
                        <input type="hidden" name="department_id" id="survey_department_id" value="{{ $currentUser->department_id }}">

                        <div class="form-group">
                            <label class="form-label">نطاق الاستبيان</label>
                            <input type="text" value="استبيان على مستوى القسم" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">القسم</label>
                            <input type="text" value="{{ $departments->first()?->name_ar }}" disabled>
                        </div>
                    @endif
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">استخدام قالب جاهز</label>
                        <select name="template_id" id="template_id">
                            <option value="">عدم استخدام قالب</option>
                        </select>
                        <small style="display:block; margin-top:8px; color:#6b7280;">
                            سيتم عرض القوالب المناسبة تلقائيًا حسب نوع الاستبيان وصلاحية الحساب.
                        </small>
                    </div>
                </div>

                @if($isDepartmentAdmin)
                    <div id="course-offering-wrapper" style="display: {{ $surveyScope === 'course' ? 'block' : 'none' }};">
                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">كلية المقرر</label>
                                <select id="faculty_id">
                                    <option value="">اختر الكلية</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}"
                                            {{ old('faculty_id', $selectedFaculty?->id) == $faculty->id ? 'selected' : '' }}>
                                            {{ $faculty->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">القسم الأكاديمي للمقرر</label>
                                <select id="department_id">
                                    <option value="">اختر القسم</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">المقرر</label>
                                <select id="course_id">
                                    <option value="">اختر المقرر</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">المادة المسجلة</label>
                                <select name="course_offering_id" id="course_offering_id">
                                    <option value="">اختر المادة المسجلة</option>
                                </select>
                            </div>
                        </div>

                        <div id="offering-preview" class="card" style="display:none; margin-bottom:16px; background:#f8f9ff; border-color:#d9def7;">
                            <div class="card-body" id="offering-preview-content"></div>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">وصف الاستبيان</label>
                    <textarea name="description">{{ old('description', $survey->description) }}</textarea>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">عدد الردود المتوقع</label>
                        <input type="number" name="expected_responses" min="1" value="{{ old('expected_responses', $survey->expected_responses) }}" placeholder="مثال: 100">
                    </div>

                    <div class="checkbox-row" style="margin-top: 32px;">
                        <input type="checkbox" id="auto_close_on_limit" name="auto_close_on_limit" {{ old('auto_close_on_limit', $survey->auto_close_on_limit) ? 'checked' : '' }}>
                        <label for="auto_close_on_limit" style="margin:0;">إغلاق الاستبيان تلقائيًا عند الوصول إلى العدد المطلوب</label>
                    </div>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $survey->is_active) ? 'checked' : '' }}>
                    <label for="is_active" style="margin:0;">تفعيل الاستبيان</label>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="allow_multiple_submissions" name="allow_multiple_submissions" {{ old('allow_multiple_submissions', $survey->allow_multiple_submissions) ? 'checked' : '' }}>
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
                        <input type="text" name="comments_question" value="{{ old('comments_question', $standaloneQuestion?->question_text ?? 'تعليقات أخرى') }}">
                    </div>
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-secondary">إلغاء</a>
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
        @foreach($survey->sections as $section)
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

<script>
    const departmentsData = @json($departmentsJson);
    const coursesData = @json($coursesJson);
    const offeringsData = @json($offeringsJson);
    const templatesData = @json($templatesJson);

    const oldTemplateId = @json(old('template_id'));
    const initialTemplateId = oldTemplateId || @json($survey->template_id ?? '');
    const oldSurveyScope = @json($surveyScope);

    const semesterLabels = {
        first: 'الفصل الدراسي الأول',
        second: 'الفصل الدراسي الثاني',
        summer: 'الفصل الصيفي',
    };

    const facultySelect = document.getElementById('faculty_id');
    const departmentSelect = document.getElementById('department_id');
    const courseSelect = document.getElementById('course_id');
    const offeringSelect = document.getElementById('course_offering_id');
    const offeringPreview = document.getElementById('offering-preview');
    const offeringPreviewContent = document.getElementById('offering-preview-content');

    const surveyScopeSelect = document.getElementById('survey_scope');
    const courseOfferingWrapper = document.getElementById('course-offering-wrapper');

    const scopeLevelSelect = document.getElementById('scope_level');
    const templateSelect = document.getElementById('template_id');
    const manualBuilderWrapper = document.getElementById('manual-builder-wrapper');

    const selectedDepartmentId = @json(old('department_id', $selectedDepartment?->id));
    const selectedCourseId = @json(old('course_id', $selectedCourse?->id));
    const selectedOfferingId = @json(old('course_offering_id', $survey->course_offering_id));

    function getCurrentTemplateScope() {
        if (surveyScopeSelect && surveyScopeSelect.value === 'course' && {{ $isDepartmentAdmin ? 'true' : 'false' }}) {
            return 'course';
        }

        if (scopeLevelSelect) {
            return scopeLevelSelect.value;
        }

        return 'department';
    }

    function resetSelect(select, placeholder) {
        if (!select) return;
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function populateDepartments(selected = null) {
        resetSelect(departmentSelect, 'اختر القسم');
        resetSelect(courseSelect, 'اختر المقرر');
        resetSelect(offeringSelect, 'اختر المادة المسجلة');
        if (offeringPreview) offeringPreview.style.display = 'none';
        if (offeringPreviewContent) offeringPreviewContent.innerHTML = '';

        const facultyId = facultySelect?.value;
        if (!facultyId || !departmentSelect) return;

        const filteredDepartments = departmentsData.filter(
            department => String(department.faculty_id) === String(facultyId)
        );

        filteredDepartments.forEach(department => {
            const option = document.createElement('option');
            option.value = department.id;
            option.textContent = department.name_ar;

            if (selected && String(selected) === String(department.id)) {
                option.selected = true;
            }

            departmentSelect.appendChild(option);
        });
    }

    function populateCourses(selected = null) {
        resetSelect(courseSelect, 'اختر المقرر');
        resetSelect(offeringSelect, 'اختر المادة المسجلة');
        if (offeringPreview) offeringPreview.style.display = 'none';
        if (offeringPreviewContent) offeringPreviewContent.innerHTML = '';

        const departmentId = departmentSelect?.value;
        if (!departmentId || !courseSelect) return;

        const filteredCourses = coursesData.filter(
            course => String(course.department_id) === String(departmentId)
        );

        filteredCourses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = `${course.name_ar}${course.code ? ' - ' + course.code : ''}`;

            if (selected && String(selected) === String(course.id)) {
                option.selected = true;
            }

            courseSelect.appendChild(option);
        });
    }

    function populateOfferings(selected = null) {
        resetSelect(offeringSelect, 'اختر المادة المسجلة');
        if (offeringPreview) offeringPreview.style.display = 'none';
        if (offeringPreviewContent) offeringPreviewContent.innerHTML = '';

        const courseId = courseSelect?.value;
        if (!courseId || !offeringSelect) return;

        const filteredOfferings = offeringsData.filter(
            offering => String(offering.course_id) === String(courseId)
        );

        filteredOfferings.forEach(offering => {
            const option = document.createElement('option');
            option.value = offering.id;
            option.textContent = `${offering.academic_year} - ${semesterLabels[offering.semester] ?? offering.semester} - ${offering.level}`;

            if (selected && String(selected) === String(offering.id)) {
                option.selected = true;
            }

            offeringSelect.appendChild(option);
        });

        showOfferingPreview();
    }

    function showOfferingPreview() {
        if (!offeringPreview || !offeringPreviewContent || !offeringSelect) return;

        offeringPreview.style.display = 'none';
        offeringPreviewContent.innerHTML = '';

        const selectedId = offeringSelect.value;
        if (!selectedId) return;

        const selectedOffering = offeringsData.find(
            offering => String(offering.id) === String(selectedId)
        );

        if (!selectedOffering) return;

        offeringPreviewContent.innerHTML = `
            <strong>بيانات المادة المسجلة:</strong><br>
            العام الدراسي: ${selectedOffering.academic_year}<br>
            الفصل الدراسي: ${semesterLabels[selectedOffering.semester] ?? selectedOffering.semester}<br>
            الفرقة: ${selectedOffering.level}<br>
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

            if (facultySelect) facultySelect.value = '';
            if (departmentSelect) resetSelect(departmentSelect, 'اختر القسم');
            if (courseSelect) resetSelect(courseSelect, 'اختر المقرر');
            if (offeringSelect) resetSelect(offeringSelect, 'اختر المادة المسجلة');
            if (offeringPreview) offeringPreview.style.display = 'none';
            if (offeringPreviewContent) offeringPreviewContent.innerHTML = '';
        }

        populateTemplates();
    }

    function populateTemplates() {
        if (!templateSelect) return;

        const currentScope = getCurrentTemplateScope();
        const currentValue = templateSelect.value || initialTemplateId || '';

        templateSelect.innerHTML = '<option value="">عدم استخدام قالب</option>';

        const filteredTemplates = templatesData.filter(template => {
            return template.scope_level === currentScope;
        });

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
        manualBuilderWrapper.style.display = templateSelect.value ? 'none' : 'block';
    }

    facultySelect?.addEventListener('change', () => populateDepartments());
    departmentSelect?.addEventListener('change', () => populateCourses());
    courseSelect?.addEventListener('change', () => populateOfferings());
    offeringSelect?.addEventListener('change', showOfferingPreview);

    surveyScopeSelect?.addEventListener('change', toggleSurveyScope);
    templateSelect?.addEventListener('change', toggleTemplateMode);

    toggleSurveyScope();
    populateTemplates();

    if (surveyScopeSelect && surveyScopeSelect.value === 'course' && facultySelect?.value) {
        populateDepartments(selectedDepartmentId);
        populateCourses(selectedCourseId);
        populateOfferings(selectedOfferingId);
    }
</script>
@endpush