<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyTemplateQuestionOption;
use App\Models\SurveyTemplateSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            $templates = SurveyTemplate::with(['faculty', 'department'])
                ->withCount(['sections', 'questions'])
                ->where('scope_level', 'university')
                ->latest()
                ->get();
        } elseif ($user->isFacultyAdmin()) {
            $templates = SurveyTemplate::with(['faculty', 'department'])
                ->withCount(['sections', 'questions'])
                ->where(function ($query) use ($user) {
                    $query->where(function ($q) use ($user) {
                        $q->where('scope_level', 'faculty')
                            ->where('faculty_id', $user->faculty_id);
                    })->orWhere(function ($q) use ($user) {
                        $q->where('scope_level', 'course')
                            ->where('faculty_id', $user->faculty_id);
                    });
                })
                ->latest()
                ->get();
        } elseif ($user->isDepartmentAdmin()) {
            $templates = SurveyTemplate::with(['faculty', 'department'])
                ->withCount(['sections', 'questions'])
                ->where('scope_level', 'department')
                ->where('department_id', $user->department_id)
                ->latest()
                ->get();
        } else {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        [$faculties, $departments] = $this->getScopeDataForCurrentUser();

        return view('admin.templates.create', compact('faculties', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplateRequest($request);
        $user = auth()->user();

        DB::transaction(function () use ($validated, $request, $user) {
            [$scopeLevel, $facultyId, $departmentId] = $this->resolveTemplateScope($validated, $user);

            if (!$facultyId && $departmentId) {
                $facultyId = Department::where('id', $departmentId)->value('faculty_id');
            }

            $template = SurveyTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'scope_level' => $scopeLevel,
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'is_active' => $request->has('is_active'),
            ]);

            $this->saveSectionsAndQuestions($template, $validated, $request->comments_question);
        });

        return redirect()->route('admin.templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح');
    }

    public function edit(SurveyTemplate $template)
    {
        $this->authorizeTemplateManagement($template);

        $template->load([
            'faculty',
            'department',
            'sections.questions.options',
            'questions.options',
        ]);

        [$faculties, $departments] = $this->getScopeDataForCurrentUser();

        return view('admin.templates.edit', compact('template', 'faculties', 'departments'));
    }

    public function update(Request $request, SurveyTemplate $template)
    {
        $this->authorizeTemplateManagement($template);

        $validated = $this->validateTemplateRequest($request);
        $user = auth()->user();

        DB::transaction(function () use ($validated, $request, $template, $user) {
            [$scopeLevel, $facultyId, $departmentId] = $this->resolveTemplateScope($validated, $user);

            if (!$facultyId && $departmentId) {
                $facultyId = Department::where('id', $departmentId)->value('faculty_id');
            }

            $template->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'scope_level' => $scopeLevel,
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'is_active' => $request->has('is_active'),
            ]);

            SurveyTemplateQuestion::where('survey_template_id', $template->id)->delete();
            SurveyTemplateSection::where('survey_template_id', $template->id)->delete();

            $this->saveSectionsAndQuestions($template, $validated, $request->comments_question);
        });

        return redirect()->route('admin.templates.index')
            ->with('success', 'تم تعديل القالب بنجاح');
    }

    public function destroy(SurveyTemplate $template)
    {
        $this->authorizeTemplateManagement($template);

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'تم حذف القالب بنجاح');
    }

    private function validateTemplateRequest(Request $request): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'scope_level' => 'nullable|in:university,faculty,department,course',
            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',

            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.questions' => 'required|array|min:1',
            'sections.*.questions.*.question_text' => 'required|string',
            'sections.*.questions.*.type' => 'required|in:scale,mcq,text',
            'sections.*.questions.*.options' => 'nullable|array',
        ];

        $validated = $request->validate($rules);
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            $validated['scope_level'] = 'university';
            $validated['faculty_id'] = null;
            $validated['department_id'] = null;
        } elseif ($user->isFacultyAdmin()) {
            $requestedScope = $validated['scope_level'] ?? 'faculty';

            if (!in_array($requestedScope, ['faculty', 'course'], true)) {
                $requestedScope = 'faculty';
            }

            $validated['scope_level'] = $requestedScope;
            $validated['faculty_id'] = $user->faculty_id;
            $validated['department_id'] = null;
        } elseif ($user->isDepartmentAdmin()) {
            $validated['scope_level'] = 'department';
            $validated['faculty_id'] = $user->faculty_id;
            $validated['department_id'] = $user->department_id;
        } else {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        if (($validated['scope_level'] ?? null) === 'faculty' && empty($validated['faculty_id'])) {
            return back()->withErrors([
                'faculty_id' => 'يجب تحديد الكلية للقالب على مستوى الكلية.',
            ])->withInput()->throwResponse();
        }

        if (($validated['scope_level'] ?? null) === 'department' && empty($validated['department_id'])) {
            return back()->withErrors([
                'department_id' => 'يجب تحديد القسم للقالب على مستوى القسم.',
            ])->withInput()->throwResponse();
        }

        if (($validated['scope_level'] ?? null) === 'course' && empty($validated['faculty_id'])) {
            return back()->withErrors([
                'faculty_id' => 'يجب تحديد الكلية لقالب استبيان المادة.',
            ])->withInput()->throwResponse();
        }

        return $validated;
    }

    private function saveSectionsAndQuestions(SurveyTemplate $template, array $validated, ?string $commentsQuestion = null): void
    {
        foreach ($validated['sections'] as $sectionIndex => $sectionData) {
            $section = SurveyTemplateSection::create([
                'survey_template_id' => $template->id,
                'title' => $sectionData['title'],
                'display_order' => $sectionIndex + 1,
            ]);

            foreach ($sectionData['questions'] as $questionIndex => $questionData) {
                $question = SurveyTemplateQuestion::create([
                    'survey_template_id' => $template->id,
                    'survey_template_section_id' => $section->id,
                    'question_text' => $questionData['question_text'],
                    'type' => $questionData['type'],
                    'is_required' => isset($questionData['is_required']),
                    'display_order' => $questionIndex + 1,
                ]);

                if (in_array($questionData['type'], ['scale', 'mcq'], true)) {
                    $options = $questionData['options'] ?? [];

                    foreach ($options as $optionIndex => $optionText) {
                        if (!filled($optionText)) {
                            continue;
                        }

                        SurveyTemplateQuestionOption::create([
                            'survey_template_question_id' => $question->id,
                            'option_text' => $optionText,
                            'option_value' => $questionData['type'] === 'scale' ? $optionIndex + 1 : null,
                            'display_order' => $optionIndex + 1,
                        ]);
                    }
                }
            }
        }

        if (filled($commentsQuestion)) {
            SurveyTemplateQuestion::create([
                'survey_template_id' => $template->id,
                'survey_template_section_id' => null,
                'question_text' => $commentsQuestion,
                'type' => 'text',
                'is_required' => false,
                'display_order' => 999,
            ]);
        }
    }

    private function getScopeDataForCurrentUser(): array
    {
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            $faculties = Faculty::orderBy('name_ar')->get();
            $departments = Department::with('faculty')->orderBy('name_ar')->get();
        } elseif ($user->isFacultyAdmin()) {
            $faculties = Faculty::where('id', $user->faculty_id)->orderBy('name_ar')->get();
            $departments = Department::with('faculty')
                ->where('faculty_id', $user->faculty_id)
                ->orderBy('name_ar')
                ->get();
        } elseif ($user->isDepartmentAdmin()) {
            $faculties = Faculty::where('id', $user->faculty_id)->orderBy('name_ar')->get();
            $departments = Department::with('faculty')
                ->where('id', $user->department_id)
                ->orderBy('name_ar')
                ->get();
        } else {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        return [$faculties, $departments];
    }

    private function resolveTemplateScope(array $validated, $user): array
    {
        if ($user->isUniversityAdmin()) {
            return ['university', null, null];
        }

        if ($user->isFacultyAdmin()) {
            $scope = $validated['scope_level'] ?? 'faculty';

            if ($scope === 'course') {
                return ['course', $user->faculty_id, null];
            }

            return ['faculty', $user->faculty_id, null];
        }

        if ($user->isDepartmentAdmin()) {
            return ['department', $user->faculty_id, $user->department_id];
        }

        abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
    }

    private function authorizeTemplateManagement(SurveyTemplate $template): void
    {
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            if ($template->scope_level === 'university') {
                return;
            }

            abort(403, 'ليس لديك صلاحية تعديل هذا القالب');
        }

        if ($user->isFacultyAdmin()) {
            if (
                (
                    $template->scope_level === 'faculty'
                    || $template->scope_level === 'course'
                ) && (int) $template->faculty_id === (int) $user->faculty_id
            ) {
                return;
            }

            abort(403, 'ليس لديك صلاحية تعديل هذا القالب');
        }

        if ($user->isDepartmentAdmin()) {
            if ($template->scope_level === 'department' && (int) $template->department_id === (int) $user->department_id) {
                return;
            }

            abort(403, 'ليس لديك صلاحية تعديل هذا القالب');
        }

        abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
    }
}