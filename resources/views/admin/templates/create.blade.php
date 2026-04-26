@extends('layouts.admin')

@php
    $pageTitle = 'إنشاء قالب جديد';
    $pageSubtitle = 'إضافة قالب استبيان جديد';
@endphp

@section('content')
    @php
        $currentUser = auth()->user();
        $isUniversityAdmin = $currentUser->isUniversityAdmin();
        $isFacultyAdmin = $currentUser->isFacultyAdmin();
        $isDepartmentAdmin = $currentUser->isDepartmentAdmin();
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">الرجوع إلى القوالب</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.templates.store') }}">
                @csrf

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">اسم القالب</label>
                        <input type="text" name="name" value="{{ old('name') }}">
                    </div>

                    <div class="checkbox-row" style="margin-top: 32px;">
                        <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" style="margin:0;">تفعيل القالب</label>
                    </div>
                </div>

                <div class="grid-2">
                    @if($isUniversityAdmin)
                        <input type="hidden" name="scope_level" value="university">

                        <div class="form-group">
                            <label class="form-label">نوع القالب</label>
                            <input type="text" value="قالب على مستوى الجامعة" disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ملاحظة</label>
                            <input type="text" value="أدمن الجامعة ينشئ قوالب الجامعة فقط" disabled>
                        </div>
                    @elseif($isFacultyAdmin)
                        <input type="hidden" name="faculty_id" value="{{ $currentUser->faculty_id }}">

                        <div class="form-group">
                            <label class="form-label">نوع القالب</label>
                            <select name="scope_level" id="scope_level">
                                <option value="faculty" {{ old('scope_level', 'faculty') === 'faculty' ? 'selected' : '' }}>قالب استبيان كلية</option>
                                <option value="course" {{ old('scope_level') === 'course' ? 'selected' : '' }}>قالب استبيان مادة</option>
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
                        <input type="hidden" name="scope_level" value="department">
                        <input type="hidden" name="faculty_id" value="{{ $currentUser->faculty_id }}">
                        <input type="hidden" name="department_id" value="{{ $currentUser->department_id }}">

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
                    <textarea name="description">{{ old('description') }}</textarea>
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
                    <input type="text" name="comments_question" value="{{ old('comments_question', 'تعليقات أخرى') }}">
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ القالب</button>
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

    function addSection() {
        const wrapper = document.getElementById('sections-wrapper');

        const html = `
            <div class="section-box" id="section-${sectionIndex}">
                <div class="section-title-inline">محور جديد</div>

                <div class="form-group">
                    <label class="form-label">عنوان المحور</label>
                    <input type="text" name="sections[${sectionIndex}][title]">
                </div>

                <div id="questions-wrapper-${sectionIndex}"></div>

                <div class="page-actions">
                    <button type="button" class="btn btn-primary" onclick="addQuestion(${sectionIndex})">إضافة سؤال</button>
                    <button type="button" class="btn btn-danger" onclick="removeElement('section-${sectionIndex}')">حذف المحور</button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);
        sectionIndex++;
    }

    function addQuestion(sectionIdx) {
        const wrapper = document.getElementById(`questions-wrapper-${sectionIdx}`);
        const questionCount = wrapper.querySelectorAll('.question-box').length;

        const html = `
            <div class="question-box" id="section-${sectionIdx}-question-${questionCount}">
                <div class="form-group">
                    <label class="form-label">نص السؤال</label>
                    <textarea name="sections[${sectionIdx}][questions][${questionCount}][question_text]"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">نوع السؤال</label>
                    <select name="sections[${sectionIdx}][questions][${questionCount}][type]" onchange="toggleOptions(this, ${sectionIdx}, ${questionCount})">
                        <option value="scale">تقييم 1-5</option>
                        <option value="mcq">اختيار من متعدد</option>
                        <option value="text">نص مفتوح</option>
                    </select>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="required-${sectionIdx}-${questionCount}" name="sections[${sectionIdx}][questions][${questionCount}][is_required]" checked>
                    <label for="required-${sectionIdx}-${questionCount}" style="margin:0;">سؤال إجباري</label>
                </div>

                <div class="form-group options-box" id="options-box-${sectionIdx}-${questionCount}">
                    <label class="form-label">الخيارات</label>
                    <div class="option-input">
                        <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="غير موافق بشدة">
                    </div>
                    <div class="option-input">
                        <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="غير موافق">
                    </div>
                    <div class="option-input">
                        <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="محايد">
                    </div>
                    <div class="option-input">
                        <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="أوافق">
                    </div>
                    <div class="option-input">
                        <input type="text" name="sections[${sectionIdx}][questions][${questionCount}][options][]" value="أوافق بشدة">
                    </div>
                </div>

                <div class="page-actions">
                    <button type="button" class="btn btn-danger" onclick="removeElement('section-${sectionIdx}-question-${questionCount}')">حذف السؤال</button>
                </div>
            </div>
        `;

        wrapper.insertAdjacentHTML('beforeend', html);
    }

    function toggleOptions(selectEl, sectionIdx, questionIdx) {
        const box = document.getElementById(`options-box-${sectionIdx}-${questionIdx}`);
        box.style.display = selectEl.value === 'text' ? 'none' : 'block';
    }

    function removeElement(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    addSection();
</script>
@endpush