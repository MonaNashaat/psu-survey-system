@extends('layouts.admin')

@php
    $pageTitle = 'إضافة مستخدم';
    $pageSubtitle = 'إنشاء مستخدم جديد وتحديد نوعه وصلاحياته';
@endphp

@section('content')
    @php
        $departmentsJson = $departments->map(function ($department) {
            return [
                'id' => $department->id,
                'name_ar' => $department->name_ar,
                'faculty_id' => $department->faculty_id,
            ];
        })->values();
    @endphp

    <div class="page-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">الرجوع إلى المستخدمين</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">نوع المستخدم</label>
                        <select name="role" id="role" required>
                            <option value="">اختر النوع</option>
                            <option value="presidency_admin" {{ old('role') == 'presidency_admin' ? 'selected' : '' }}>

                                المكتب الفني لرئيس الجامعة
                        
                            </option>
                            <option value="university_admin" {{ old('role') == 'university_admin' ? 'selected' : '' }}>أدمن جامعة</option>
                            <option value="faculty_admin" {{ old('role') == 'faculty_admin' ? 'selected' : '' }}>أدمن كلية</option>
                            <option value="department_admin" {{ old('role') == 'department_admin' ? 'selected' : '' }}>أدمن قسم</option>
                            <option value="results_viewer" {{ old('role') == 'results_viewer' ? 'selected' : '' }}>عرض نتائج فقط</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" required>
                    </div>
                </div>

                <div class="grid-2" id="faculty-department-row">
                    <div class="form-group" id="faculty-wrapper">
                        <label class="form-label">الكلية</label>
                        <select name="faculty_id" id="faculty_id">
                            <option value="">اختر الكلية</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}" {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                    {{ $faculty->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="department-wrapper">
                        <label class="form-label">القسم</label>
                        <select name="department_id" id="department_id">
                            <option value="">اختر القسم</option>
                        </select>
                    </div>
                </div>

                <div class="page-actions">
                    <button type="submit" class="btn btn-primary">حفظ المستخدم</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const departmentsData = @json($departmentsJson);
    const oldDepartmentId = @json(old('department_id'));

    const roleSelect = document.getElementById('role');
    const facultySelect = document.getElementById('faculty_id');
    const departmentSelect = document.getElementById('department_id');
    const facultyWrapper = document.getElementById('faculty-wrapper');
    const departmentWrapper = document.getElementById('department-wrapper');

    function resetDepartments() {
        departmentSelect.innerHTML = '<option value="">اختر القسم</option>';
    }

    function populateDepartments() {
        resetDepartments();

        const facultyId = facultySelect.value;
        if (!facultyId) return;

        const filteredDepartments = departmentsData.filter(
            department => String(department.faculty_id) === String(facultyId)
        );

        filteredDepartments.forEach(department => {
            const option = document.createElement('option');
            option.value = department.id;
            option.textContent = department.name_ar;

            if (String(oldDepartmentId) === String(department.id)) {
                option.selected = true;
            }

            departmentSelect.appendChild(option);
        });
    }

    function toggleRoleFields() {
        const role = roleSelect.value;

        facultyWrapper.style.display = 'none';
        departmentWrapper.style.display = 'none';

        if (role === 'faculty_admin') {
            facultyWrapper.style.display = 'block';
            departmentWrapper.style.display = 'none';
            resetDepartments();
        } else if (role === 'department_admin') {
            facultyWrapper.style.display = 'block';
            departmentWrapper.style.display = 'block';
            populateDepartments();
        } else {
            facultySelect.value = '';
            resetDepartments();
        }
    }

    roleSelect.addEventListener('change', toggleRoleFields);
    facultySelect.addEventListener('change', populateDepartments);

    toggleRoleFields();
</script>
@endpush