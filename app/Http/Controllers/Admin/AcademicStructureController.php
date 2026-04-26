<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Imports\CoursesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CourseOfferingsImport;

class AcademicStructureController extends Controller
{
    protected function user()
    {
        return Auth::user();
    }

    protected function ensureUniversityAdmin(): void
    {
        abort_unless($this->user()?->role === 'university_admin', 403);
    }

    protected function ensureFacultyOrUniversityAdmin(): void
    {
        abort_unless(in_array($this->user()?->role, ['university_admin', 'faculty_admin'], true), 403);
    }

    protected function ensureDepartmentFacultyOrUniversityAdmin(): void
    {
        abort_unless(in_array($this->user()?->role, ['university_admin', 'faculty_admin', 'department_admin'], true), 403);
    }

    protected function visibleFacultiesQuery()
    {
        $user = $this->user();

        if ($user->role === 'faculty_admin' && $user->faculty_id) {
            return Faculty::query()->where('id', $user->faculty_id);
        }

        if ($user->role === 'department_admin' && $user->faculty_id) {
            return Faculty::query()->where('id', $user->faculty_id);
        }

        return Faculty::query();
    }

    protected function visibleDepartmentsQuery()
    {
        $user = $this->user();

        if ($user->role === 'faculty_admin' && $user->faculty_id) {
            return Department::query()->with('faculty')->where('faculty_id', $user->faculty_id);
        }

        if ($user->role === 'department_admin' && $user->department_id) {
            return Department::query()->with('faculty')->where('id', $user->department_id);
        }

        return Department::query()->with('faculty');
    }

    protected function visibleCoursesQuery()
    {
        $user = $this->user();

        if ($user->role === 'faculty_admin' && $user->faculty_id) {
            return Course::query()
                ->with('department.faculty')
                ->whereHas('department', function ($query) use ($user) {
                    $query->where('faculty_id', $user->faculty_id);
                });
        }

        if ($user->role === 'department_admin' && $user->department_id) {
            return Course::query()
                ->with('department.faculty')
                ->where('department_id', $user->department_id);
        }

        return Course::query()->with('department.faculty');
    }

    protected function visibleOfferingsQuery()
    {
        $user = $this->user();

        if ($user->role === 'faculty_admin' && $user->faculty_id) {
            return CourseOffering::query()
                ->with('course.department.faculty')
                ->whereHas('course.department', function ($query) use ($user) {
                    $query->where('faculty_id', $user->faculty_id);
                });
        }

        if ($user->role === 'department_admin' && $user->department_id) {
            return CourseOffering::query()
                ->with('course.department.faculty')
                ->whereHas('course', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                });
        }

        return CourseOffering::query()->with('course.department.faculty');
    }

    protected function ensureDepartmentBelongsToScope(int $departmentId): Department
    {
        $user = $this->user();

        $department = Department::with('faculty')->findOrFail($departmentId);

        if ($user->role === 'faculty_admin') {
            abort_unless((int) $department->faculty_id === (int) $user->faculty_id, 403);
        }

        if ($user->role === 'department_admin') {
            abort_unless((int) $department->id === (int) $user->department_id, 403);
        }

        return $department;
    }

    protected function ensureFacultyBelongsToScope(int $facultyId): Faculty
    {
        $user = $this->user();

        $faculty = Faculty::findOrFail($facultyId);

        if ($user->role === 'faculty_admin') {
            abort_unless((int) $faculty->id === (int) $user->faculty_id, 403);
        }

        if ($user->role === 'department_admin') {
            abort(403);
        }

        return $faculty;
    }

    protected function ensureCourseBelongsToScope(int $courseId): Course
    {
        $user = $this->user();

        $course = Course::with('department.faculty')->findOrFail($courseId);

        if ($user->role === 'faculty_admin') {
            abort_unless((int) $course->department->faculty_id === (int) $user->faculty_id, 403);
        }

        if ($user->role === 'department_admin') {
            abort_unless((int) $course->department_id === (int) $user->department_id, 403);
        }

        return $course;
    }

    protected function ensureOfferingBelongsToScope(int $offeringId): CourseOffering
    {
        $user = $this->user();

        $offering = CourseOffering::with('course.department.faculty')->findOrFail($offeringId);

        if ($user->role === 'faculty_admin') {
            abort_unless((int) $offering->course->department->faculty_id === (int) $user->faculty_id, 403);
        }

        if ($user->role === 'department_admin') {
            abort_unless((int) $offering->course->department_id === (int) $user->department_id, 403);
        }

        return $offering;
    }

    public function facultiesIndex()
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $faculties = $this->visibleFacultiesQuery()->latest()->get();

        return view('admin.academic.faculties.index', compact('faculties'));
    }

    public function facultiesStore(Request $request)
    {
        $this->ensureUniversityAdmin();

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        Faculty::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'تم إضافة الكلية بنجاح');
    }

    public function facultiesEdit(Faculty $faculty)
    {
        $this->ensureUniversityAdmin();

        return view('admin.academic.faculties.edit', compact('faculty'));
    }

    public function facultiesUpdate(Request $request, Faculty $faculty)
    {
        $this->ensureUniversityAdmin();

        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $faculty->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'تم تعديل الكلية بنجاح');
    }

    public function facultiesDestroy(Faculty $faculty)
    {
        $this->ensureUniversityAdmin();

        if ($faculty->departments()->exists()) {
            return redirect()->route('admin.academic.faculties.index')
                ->with('error', 'لا يمكن حذف الكلية لوجود أقسام مرتبطة بها');
        }

        $faculty->delete();

        return redirect()->route('admin.academic.faculties.index')
            ->with('success', 'تم حذف الكلية بنجاح');
    }

    public function departmentsIndex()
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $departments = $this->visibleDepartmentsQuery()->latest()->get();
        $faculties = $this->visibleFacultiesQuery()->orderBy('name_ar')->get();

        return view('admin.academic.departments.index', compact('departments', 'faculties'));
    }

    public function departmentsStore(Request $request)
    {
        $this->ensureFacultyOrUniversityAdmin();

        $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $faculty = $this->ensureFacultyBelongsToScope((int) $request->faculty_id);

        Department::create([
            'faculty_id' => $faculty->id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.departments.index')
            ->with('success', 'تم إضافة القسم بنجاح');
    }

    public function departmentsEdit(Department $department)
    {
        $this->ensureFacultyOrUniversityAdmin();
        $department = $this->ensureDepartmentBelongsToScope($department->id);
        $faculties = $this->visibleFacultiesQuery()->orderBy('name_ar')->get();

        return view('admin.academic.departments.edit', compact('department', 'faculties'));
    }

    public function departmentsUpdate(Request $request, Department $department)
    {
        $this->ensureFacultyOrUniversityAdmin();
        $department = $this->ensureDepartmentBelongsToScope($department->id);

        $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $faculty = $this->ensureFacultyBelongsToScope((int) $request->faculty_id);

        $department->update([
            'faculty_id' => $faculty->id,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.departments.index')
            ->with('success', 'تم تعديل القسم بنجاح');
    }

    public function departmentsDestroy(Department $department)
    {
        $this->ensureFacultyOrUniversityAdmin();
        $department = $this->ensureDepartmentBelongsToScope($department->id);

        if ($department->courses()->exists()) {
            return redirect()->route('admin.academic.departments.index')
                ->with('error', 'لا يمكن حذف القسم لوجود مقررات مرتبطة به');
        }

        $department->delete();

        return redirect()->route('admin.academic.departments.index')
            ->with('success', 'تم حذف القسم بنجاح');
    }

    public function coursesIndex()
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $courses = $this->visibleCoursesQuery()->latest()->get();
        $departments = $this->visibleDepartmentsQuery()->orderBy('name_ar')->get();

        return view('admin.academic.courses.index', compact('courses', 'departments'));
    }

    public function coursesStore(Request $request)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $department = $this->ensureDepartmentBelongsToScope((int) $request->department_id);

        Course::create([
            'department_id' => $department->id,
            'code' => $request->code,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'تم إضافة المقرر بنجاح');
    }

    public function coursesEdit(Course $course)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $course = $this->ensureCourseBelongsToScope($course->id);
        $departments = $this->visibleDepartmentsQuery()->orderBy('name_ar')->get();

        return view('admin.academic.courses.edit', compact('course', 'departments'));
    }

    public function coursesUpdate(Request $request, Course $course)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $course = $this->ensureCourseBelongsToScope($course->id);

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'code' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
        ]);

        $department = $this->ensureDepartmentBelongsToScope((int) $request->department_id);

        $course->update([
            'department_id' => $department->id,
            'code' => $request->code,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
        ]);

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'تم تعديل المقرر بنجاح');
    }

    public function coursesDestroy(Course $course)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $course = $this->ensureCourseBelongsToScope($course->id);

        if ($course->offerings()->exists()) {
            return redirect()->route('admin.academic.courses.index')
                ->with('error', 'لا يمكن حذف المقرر لوجود طروحات دراسية مرتبطة به');
        }

        $course->delete();

        return redirect()->route('admin.academic.courses.index')
            ->with('success', 'تم حذف المقرر بنجاح');
    }

    public function offeringsIndex()
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $offerings = $this->visibleOfferingsQuery()->latest()->get();
        $courses = $this->visibleCoursesQuery()->orderBy('name_ar')->get();

        return view('admin.academic.offerings.index', compact('offerings', 'courses'));
    }

    public function offeringsStore(Request $request)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|in:first,second,summer',
            'level' => 'required|string|max:255',
            'instructor_name' => 'nullable|string|max:255',
            'assistant_name' => 'nullable|string|max:255',
        ]);

        $course = $this->ensureCourseBelongsToScope((int) $request->course_id);

        CourseOffering::create([
            'course_id' => $course->id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'level' => $request->level,
            'instructor_name' => $request->instructor_name,
            'assistant_name' => $request->assistant_name,
        ]);

        return redirect()->route('admin.academic.offerings.index')
            ->with('success', 'تم إضافة الطرح الدراسي بنجاح');
    }

    public function offeringsEdit(CourseOffering $offering)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $offering = $this->ensureOfferingBelongsToScope($offering->id);
        $courses = $this->visibleCoursesQuery()->orderBy('name_ar')->get();

        return view('admin.academic.offerings.edit', compact('offering', 'courses'));
    }

    public function offeringsUpdate(Request $request, CourseOffering $offering)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $offering = $this->ensureOfferingBelongsToScope($offering->id);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|in:first,second,summer',
            'level' => 'required|string|max:255',
            'instructor_name' => 'nullable|string|max:255',
            'assistant_name' => 'nullable|string|max:255',
        ]);

        $course = $this->ensureCourseBelongsToScope((int) $request->course_id);

        $offering->update([
            'course_id' => $course->id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'level' => $request->level,
            'instructor_name' => $request->instructor_name,
            'assistant_name' => $request->assistant_name,
        ]);

        return redirect()->route('admin.academic.offerings.index')
            ->with('success', 'تم تعديل الطرح الدراسي بنجاح');
    }

    public function offeringsDestroy(CourseOffering $offering)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();
        $offering = $this->ensureOfferingBelongsToScope($offering->id);

        if ($offering->surveys()->exists()) {
            return redirect()->route('admin.academic.offerings.index')
                ->with('error', 'لا يمكن حذف الطرح الدراسي لوجود استبيانات مرتبطة به');
        }

        $offering->delete();

        return redirect()->route('admin.academic.offerings.index')
            ->with('success', 'تم حذف الطرح الدراسي بنجاح');
    }
    public function coursesImport(Request $request)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new CoursesImport($this->user()), $request->file('file'));

            return redirect()->route('admin.academic.courses.index')
                ->with('success', 'تم استيراد المقررات بنجاح');
        } catch (\Throwable $e) {
            return redirect()->route('admin.academic.courses.index')
                ->with('error', 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage());
        }
    }
    public function offeringsImport(Request $request)
    {
        $this->ensureDepartmentFacultyOrUniversityAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new CourseOfferingsImport($this->user()), $request->file('file'));

            return redirect()->route('admin.academic.offerings.index')
                ->with('success', 'تم استيراد المواد المسجلة بنجاح');
        } catch (\Throwable $e) {
            return redirect()->route('admin.academic.offerings.index')
                ->with('error', 'حدث خطأ أثناء استيراد المواد المسجلة: ' . $e->getMessage());
        }
    }
}