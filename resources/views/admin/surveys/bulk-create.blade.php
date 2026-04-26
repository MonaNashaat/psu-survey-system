@extends('layouts.admin')

@php
    $pageTitle = 'إنشاء استبيانات جماعيًا';
    $pageSubtitle = 'إنشاء استبيانات متعددة للمقررات داخل القسم بالاعتماد على قالب واحد';
@endphp

@section('content')
    <div class="page-actions">
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">الرجوع إلى الاستبيانات</a>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-secondary">إنشاء استبيان فردي</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="section-title">إنشاء استبيانات المقررات دفعة واحدة</h2>

            <form method="POST" action="{{ route('admin.surveys.bulk.store') }}">
                @csrf

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">قالب استبيان المقرر</label>
                        <select name="template_id" required>
                            <option value="">اختر القالب</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">عنوان الاستبيان</label>
                        <input type="text" name="title" value="{{ old('title', 'استبيان تقييم المقرر الدراسي') }}" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">العام الدراسي</label>
                        <select name="academic_year" required>
                            <option value="">اختر العام الدراسي</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" {{ old('academic_year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الفصل الدراسي</label>
                        <select name="semester" required>
                            <option value="">اختر الفصل الدراسي</option>
                            @foreach($semesters as $value => $label)
                                <option value="{{ $value }}" {{ old('semester') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">وصف الاستبيان</label>
                    <textarea name="description">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">عدد الردود المتوقع</label>
                    <input type="number" name="expected_responses" min="1" value="{{ old('expected_responses') }}" placeholder="مثال: 100">
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="auto_close_on_limit" name="auto_close_on_limit" {{ old('auto_close_on_limit', true) ? 'checked' : '' }}>
                    <label for="auto_close_on_limit" style="margin:0;">إغلاق الاستبيان تلقائيًا عند الوصول إلى العدد المطلوب</label>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active" style="margin:0;">تفعيل الاستبيانات بعد الإنشاء</label>
                </div>

                <div class="checkbox-row">
                    <input type="checkbox" id="allow_multiple_submissions" name="allow_multiple_submissions" {{ old('allow_multiple_submissions') ? 'checked' : '' }}>
                    <label for="allow_multiple_submissions" style="margin:0;">السماح بأكثر من رد من نفس الجهاز</label>
                </div>

                <div style="height:16px;"></div>

                <div class="card" style="background:#f8f9ff; border-color:#d9def7;">
                    <div class="card-body">
                        <strong>ملاحظات مهمة:</strong>
                        <ul style="margin:12px 0 0 18px;">
                            <li>سيتم إنشاء استبيان لكل مادة مسجلة داخل القسم في العام الدراسي والفصل المختارين.</li>
                            <li>إذا كان هناك استبيان موجود بالفعل لنفس المادة بنفس العنوان، سيتم تخطيه.</li>
                            <li>يجب أن يكون القالب من نوع «قالب مقرر دراسي» ومفعل.</li>
                        </ul>
                    </div>
                </div>

                <div class="page-actions" style="margin-top:16px;">
                    <button type="submit" class="btn btn-primary">إنشاء الاستبيانات</button>
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection