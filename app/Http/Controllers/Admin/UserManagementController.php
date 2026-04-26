<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    private function ensureUniversityAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'university_admin', 403);
    }

    public function index()
    {
        $this->ensureUniversityAdmin();

        $users = User::with(['faculty', 'department'])
            ->latest()
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->ensureUniversityAdmin();

        $faculties = Faculty::orderBy('name_ar')->get();
        $departments = Department::with('faculty')->orderBy('name_ar')->get();

        return view('admin.users.create', compact('faculties', 'departments'));
    }

    public function store(Request $request)
    {
        $this->ensureUniversityAdmin();

        $validated = $this->validateUserData($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'faculty_id' => $validated['faculty_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        $this->ensureUniversityAdmin();

        $faculties = Faculty::orderBy('name_ar')->get();
        $departments = Department::with('faculty')->orderBy('name_ar')->get();

        return view('admin.users.edit', compact('user', 'faculties', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureUniversityAdmin();

        $validated = $this->validateUserData($request, $user);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'faculty_id' => $validated['faculty_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم تعديل المستخدم بنجاح');
    }

    public function resetPassword(User $user)
    {
        $this->ensureUniversityAdmin();

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'لا يمكن إعادة تعيين كلمة المرور للحساب الحالي من هذا الزر');
        }

        $newPassword = Str::random(8);

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إعادة تعيين كلمة المرور بنجاح. كلمة المرور الجديدة: ' . $newPassword);
    }

    public function destroy(User $user)
    {
        $this->ensureUniversityAdmin();

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'لا يمكنك حذف حسابك الحالي');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }

    private function validateUserData(Request $request, ?User $user = null): array
    {
        $emailRule = 'required|email|max:255|unique:users,email';
        if ($user) {
            $emailRule .= ',' . $user->id;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'role' => 'required|in:university_admin,faculty_admin,department_admin,results_viewer',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
            'password' => $user ? 'nullable|string|min:6' : 'required|string|min:6',
        ];

        $validated = $request->validate($rules);

        if ($validated['role'] === 'university_admin') {
            $validated['faculty_id'] = null;
            $validated['department_id'] = null;
        }

        if ($validated['role'] === 'faculty_admin') {
            if (empty($validated['faculty_id'])) {
                return back()->withErrors([
                    'faculty_id' => 'يجب تحديد الكلية لأدمن الكلية.',
                ])->withInput()->throwResponse();
            }

            $validated['department_id'] = null;
        }

        if ($validated['role'] === 'department_admin') {
            if (empty($validated['faculty_id'])) {
                return back()->withErrors([
                    'faculty_id' => 'يجب تحديد الكلية لأدمن القسم.',
                ])->withInput()->throwResponse();
            }

            if (empty($validated['department_id'])) {
                return back()->withErrors([
                    'department_id' => 'يجب تحديد القسم لأدمن القسم.',
                ])->withInput()->throwResponse();
            }

            $department = Department::find($validated['department_id']);
            if (!$department || (int) $department->faculty_id !== (int) $validated['faculty_id']) {
                return back()->withErrors([
                    'department_id' => 'القسم المختار لا يتبع الكلية المحددة.',
                ])->withInput()->throwResponse();
            }
        }

        if ($validated['role'] === 'results_viewer') {
            $validated['faculty_id'] = null;
            $validated['department_id'] = null;
        }

        return $validated;
    }
}