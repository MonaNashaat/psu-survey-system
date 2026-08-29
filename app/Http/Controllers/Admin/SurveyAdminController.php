<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SurveyResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Models\SurveyPermission;
use App\Models\SurveyResponse;
use App\Models\SurveySection;
use App\Models\SurveyTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActiveSurveysExport;
use App\Services\SurveyAnalysisExcelService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SurveyAdminController extends Controller
{
    public function index()
    {
        $surveys = $this->getVisibleSurveysQuery(auth()->user())
            ->with(['faculty', 'department', 'courseOffering.course.department.faculty'])
            ->latest()
            ->get();

        return view('admin.surveys.index', compact('surveys'));
    }

    public function __construct(

        private SurveyAnalysisExcelService $surveyAnalysisExcelService

    ) {}
    public function exportAnalysis(Survey $survey): BinaryFileResponse
    {
        $this->authorizeSurveyResultsAccess($survey);
        $filePath = $this->surveyAnalysisExcelService->export($survey);

        $safeTitle = preg_replace(
            '/[^\p{L}\p{N}\-_]+/u',
            '-',
            $survey->title ?? 'survey'
        );

        $fileName = sprintf(
            'survey-%d-%s-analysis.xlsx',
            $survey->id,
            trim($safeTitle, '-')
        );

        return response()
            ->download(
                $filePath,
                $fileName,
                [
                    'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }

    public function create()
    {
        [$faculties, $departments, $courses, $courseOfferings] = $this->getAcademicDataForCurrentUser();
        $templates = $this->getTemplatesForCurrentUser();

        return view('admin.surveys.create', compact(
            'faculties',
            'departments',
            'courses',
            'courseOfferings',
            'templates'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSurveyRequest($request);
        $user = auth()->user();
        $surveyOwner = $user->isPresidencyAdmin()
            ? Survey::OWNER_PRESIDENCY
            : Survey::OWNER_QUALITY_CENTER;

        DB::transaction(function () use ($request, $validated, $user, $surveyOwner) {
            $courseOffering = $this->resolveCourseOffering($validated, $user);
            [$scopeLevel, $facultyId, $departmentId] = $this->resolveSurveyScope($validated, $user, $courseOffering);

            $department = $departmentId ? Department::find($departmentId) : null;

            $survey = Survey::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,

                'scope_level' => $scopeLevel,
                'survey_owner' => $surveyOwner,
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,

                'course_offering_id' => $courseOffering?->id,
                'course_title' => $courseOffering?->course?->name_ar,
                'department_name' => $courseOffering?->course?->department?->name_ar ?? $department?->name_ar,

                'semester' => $courseOffering?->semester,
                'level' => $courseOffering?->level,
                'academic_year' => $courseOffering?->academic_year,

                'is_active' => $request->has('is_active'),
                'allow_multiple_submissions' => $request->has('allow_multiple_submissions'),
                'expected_responses' => $validated['expected_responses'] ?? null,
                'auto_close_on_limit' => $request->has('auto_close_on_limit'),
            ]);

            if (!empty($validated['template_id'])) {
                $template = SurveyTemplate::with(['sections.questions.options', 'questions.options'])
                    ->findOrFail($validated['template_id']);

                $this->copyTemplateToSurvey($template, $survey);
            } else {
                $this->saveSectionsAndQuestions($survey, $validated, $request->comments_question);
            }
        });

        return redirect()->route('admin.surveys.index')
            ->with('success', 'تم إنشاء الاستبيان بنجاح');
    }

    public function show(Survey $survey)
    {
        $this->authorizeSurveyAccess($survey);

        $survey->load([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions',
        ]);

        return view('admin.surveys.show', compact('survey'));
    }

    public function edit(Survey $survey)
    {
        $this->authorizeSurveyManagement($survey);

        $survey->load([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions',
        ]);

        [$faculties, $departments, $courses, $courseOfferings] = $this->getAcademicDataForCurrentUser();
        $templates = $this->getTemplatesForCurrentUser();

        return view('admin.surveys.edit', compact(
            'survey',
            'faculties',
            'departments',
            'courses',
            'courseOfferings',
            'templates'
        ));
    }

    public function update(Request $request, Survey $survey)
    {
        $this->authorizeSurveyManagement($survey);

        $validated = $this->validateSurveyRequest($request);
        $user = auth()->user();

        DB::transaction(function () use ($request, $validated, $survey, $user) {
            $courseOffering = $this->resolveCourseOffering($validated, $user);
            [$scopeLevel, $facultyId, $departmentId] = $this->resolveSurveyScope($validated, $user, $courseOffering);

            $department = $departmentId ? Department::find($departmentId) : null;

            $survey->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,

                'scope_level' => $scopeLevel,
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,

                'course_offering_id' => $courseOffering?->id,
                'course_title' => $courseOffering?->course?->name_ar,
                'department_name' => $courseOffering?->course?->department?->name_ar ?? $department?->name_ar,

                'semester' => $courseOffering?->semester,
                'level' => $courseOffering?->level,
                'academic_year' => $courseOffering?->academic_year,

                'is_active' => $request->has('is_active'),
                'allow_multiple_submissions' => $request->has('allow_multiple_submissions'),
                'expected_responses' => $validated['expected_responses'] ?? null,
                'auto_close_on_limit' => $request->has('auto_close_on_limit'),
            ]);

            Question::where('survey_id', $survey->id)->delete();
            SurveySection::where('survey_id', $survey->id)->delete();

            if (!empty($validated['template_id'])) {
                $template = SurveyTemplate::with(['sections.questions.options', 'questions.options'])
                    ->findOrFail($validated['template_id']);

                $this->copyTemplateToSurvey($template, $survey);
            } else {
                $this->saveSectionsAndQuestions($survey, $validated, $request->comments_question);
            }
        });

        return redirect()->route('admin.surveys.show', $survey->id)
            ->with('success', 'تم تعديل الاستبيان بنجاح');
    }

    public function destroy(Survey $survey)
    {
        $this->authorizeSurveyManagement($survey);

        $survey->delete();

        return redirect()->route('admin.surveys.index')
            ->with('success', 'تم حذف الاستبيان بنجاح');
    }

    public function results(Survey $survey)
    {
        $this->authorizeSurveyResultsAccess($survey);

        $survey->load([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions.options',
            'responses.answers',
        ]);

        $responsesCount = $survey->responses->count();
        $questionStats = [];

        foreach ($survey->sections as $section) {
            foreach ($section->questions as $question) {
                $questionStats[$question->id] = $this->buildQuestionStats($question, $survey);
            }
        }

        $standaloneQuestions = $survey->questions->whereNull('survey_section_id');
        foreach ($standaloneQuestions as $question) {
            $questionStats[$question->id] = $this->buildQuestionStats($question, $survey);
        }

        return view('admin.surveys.results', compact('survey', 'responsesCount', 'questionStats'));
    }

    public function exportExcel(Survey $survey)
    {
        $this->authorizeSurveyResultsAccess($survey);

        return Excel::download(new SurveyResultsExport($survey), 'survey-results-' . $survey->id . '.xlsx');
    }

    public function exportPdf(Survey $survey)
    {
        $this->authorizeSurveyResultsAccess($survey);

        $survey->load([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions.options',
            'responses.answers',
        ]);

        $responsesCount = $survey->responses->count();
        $questionStats = [];

        foreach ($survey->sections as $section) {
            foreach ($section->questions as $question) {
                $questionStats[$question->id] = $this->buildQuestionStats($question, $survey);
            }
        }

        $standaloneQuestions = $survey->questions->whereNull('survey_section_id');
        foreach ($standaloneQuestions as $question) {
            $questionStats[$question->id] = $this->buildQuestionStats($question, $survey);
        }

        $pdf = Pdf::loadView('admin.surveys.results-pdf', [
            'survey' => $survey,
            'responsesCount' => $responsesCount,
            'questionStats' => $questionStats,
        ]);

        return $pdf->download('survey-results-' . $survey->id . '.pdf');
    }

    public function permissions(Survey $survey)
    {
        if (!auth()->user()->isUniversityAdmin()) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        $survey->load('permissions.user');
        $users = User::orderBy('name')->get();

        return view('admin.surveys.permissions', compact('survey', 'users'));
    }

    public function storePermission(Request $request, Survey $survey)
    {
        if (!auth()->user()->isUniversityAdmin()) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_type' => 'required|string',
        ]);

        SurveyPermission::firstOrCreate([
            'survey_id' => $survey->id,
            'user_id' => $request->user_id,
            'permission_type' => $request->permission_type,
        ]);

        return redirect()->route('admin.surveys.permissions', $survey->id)
            ->with('success', 'تم منح الصلاحية بنجاح');
    }

    public function destroyPermission(Survey $survey, SurveyPermission $permission)
    {
        if (!auth()->user()->isUniversityAdmin()) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        if ($permission->survey_id != $survey->id) {
            abort(404);
        }

        $permission->delete();

        return redirect()->route('admin.surveys.permissions', $survey->id)
            ->with('success', 'تم حذف الصلاحية بنجاح');
    }

    public function dashboard()
    {
        $user = auth()->user();

        $surveys = $this->getVisibleSurveysQuery($user)
            ->with(['faculty', 'department', 'courseOffering.course.department.faculty'])
            ->withCount('responses')
            ->latest()
            ->get();

        $surveyIds = $surveys->pluck('id');

        $totalSurveys = $surveys->count();
        $activeSurveys = $surveys->where('is_active', true)->count();
        $totalResponses = SurveyResponse::whereIn('survey_id', $surveyIds)->count();
        $latestSurveys = $surveys->take(5);
        $responsesChartLabels = $surveys->pluck('title')->values();
        $responsesChartData = $surveys->pluck('responses_count')->values();

        $averageRatings = [];
        foreach ($surveys as $survey) {
            $avg = Answer::whereHas('response', function ($query) use ($survey) {
                $query->where('survey_id', $survey->id);
            })->whereNotNull('answer_value')->avg('answer_value');

            $averageRatings[] = $avg ? round($avg, 2) : 0;
        }

        return view('admin.dashboard', compact(
            'totalSurveys',
            'activeSurveys',
            'totalResponses',
            'latestSurveys',
            'responsesChartLabels',
            'responsesChartData',
            'averageRatings'
        ));
    }

    private function validateSurveyRequest(Request $request): array
    {
        $request->merge([
            'auto_close_on_limit' => $request->has('auto_close_on_limit'),
        ]);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'nullable|exists:survey_templates,id',

            'survey_scope' => 'required|in:general,course',
            'scope_level' => 'nullable|in:university,faculty,department',

            'faculty_id' => 'nullable|exists:faculties,id',
            'department_id' => 'nullable|exists:departments,id',
            'course_offering_id' => 'nullable|exists:course_offerings,id',

            'expected_responses' => 'nullable|integer|min:1',
            'auto_close_on_limit' => 'nullable|boolean',
        ];

        if (empty($request->template_id)) {
            $rules = array_merge($rules, [
                'sections' => 'required|array|min:1',
                'sections.*.title' => 'required|string|max:255',
                'sections.*.questions' => 'required|array|min:1',
                'sections.*.questions.*.question_text' => 'required|string',
                'sections.*.questions.*.type' => 'required|in:scale,mcq,text',
                'sections.*.questions.*.options' => 'nullable|array',
            ]);
        }

        $validated = $request->validate($rules);
        $user = auth()->user();

        if ($user->isUniversityAdmin() || $user->isPresidencyAdmin()) {
            $validated['survey_scope'] = 'general';
            $validated['scope_level'] = 'university';
            $validated['faculty_id'] = null;
            $validated['department_id'] = null;
            $validated['course_offering_id'] = null;
        } elseif ($user->isFacultyAdmin()) {
            $validated['survey_scope'] = 'general';
            $validated['scope_level'] = 'faculty';
            $validated['faculty_id'] = $user->faculty_id;
            $validated['department_id'] = null;
            $validated['course_offering_id'] = null;
        } elseif ($user->isDepartmentAdmin()) {
            $validated['scope_level'] = 'department';
            $validated['faculty_id'] = $user->faculty_id;
            $validated['department_id'] = $user->department_id;

            if (($validated['survey_scope'] ?? null) !== 'course') {
                $validated['survey_scope'] = 'general';
                $validated['course_offering_id'] = null;
            }
        } else {
            abort(403, 'ليس لديك صلاحية إنشاء أو تعديل الاستبيانات');
        }

        if (($validated['survey_scope'] ?? null) === 'course' && !$user->isDepartmentAdmin()) {
            return back()->withErrors([
                'survey_scope' => 'استبيانات المواد من صلاحية أدمن القسم فقط.',
            ])->withInput()->throwResponse();
        }

        if (($validated['survey_scope'] ?? null) === 'course' && empty($validated['course_offering_id'])) {
            return back()->withErrors([
                'course_offering_id' => 'يجب اختيار المادة المسجلة عند إنشاء استبيان مرتبط بمادة.',
            ])->withInput()->throwResponse();
        }

        if (!empty($validated['template_id'])) {
            $template = SurveyTemplate::find($validated['template_id']);

            if (!$template || !$this->canUseTemplate($template, $user, $validated['survey_scope'])) {
                return back()->withErrors([
                    'template_id' => 'القالب المختار غير متاح لهذا النوع من الاستبيانات أو لهذا المستخدم.',
                ])->withInput()->throwResponse();
            }
        }

        return $validated;
    }

    private function saveSectionsAndQuestions(Survey $survey, array $validated, ?string $commentsQuestion = null): void
    {
        foreach ($validated['sections'] as $sectionIndex => $sectionData) {
            $section = SurveySection::create([
                'survey_id' => $survey->id,
                'title' => $sectionData['title'],
                'display_order' => $sectionIndex + 1,
            ]);

            foreach ($sectionData['questions'] as $questionIndex => $questionData) {
                $question = Question::create([
                    'survey_id' => $survey->id,
                    'survey_section_id' => $section->id,
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

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionText,
                            'option_value' => $questionData['type'] === 'scale' ? $optionIndex + 1 : null,
                            'display_order' => $optionIndex + 1,
                        ]);
                    }
                }
            }
        }

        if (filled($commentsQuestion)) {
            Question::create([
                'survey_id' => $survey->id,
                'survey_section_id' => null,
                'question_text' => $commentsQuestion,
                'type' => 'text',
                'is_required' => false,
                'display_order' => 999,
            ]);
        }
    }

    private function copyTemplateToSurvey(SurveyTemplate $template, Survey $survey): void
    {
        foreach ($template->sections as $sectionIndex => $templateSection) {
            $section = SurveySection::create([
                'survey_id' => $survey->id,
                'title' => $templateSection->title,
                'display_order' => $sectionIndex + 1,
            ]);

            foreach ($templateSection->questions as $questionIndex => $templateQuestion) {
                $question = Question::create([
                    'survey_id' => $survey->id,
                    'survey_section_id' => $section->id,
                    'question_text' => $templateQuestion->question_text,
                    'type' => $templateQuestion->type,
                    'is_required' => $templateQuestion->is_required,
                    'display_order' => $questionIndex + 1,
                ]);

                foreach ($templateQuestion->options as $optionIndex => $templateOption) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $templateOption->option_text,
                        'option_value' => $templateOption->option_value,
                        'display_order' => $optionIndex + 1,
                    ]);
                }
            }
        }

        $standaloneQuestions = $template->questions->whereNull('survey_template_section_id');

        foreach ($standaloneQuestions as $questionIndex => $templateQuestion) {
            $question = Question::create([
                'survey_id' => $survey->id,
                'survey_section_id' => null,
                'question_text' => $templateQuestion->question_text,
                'type' => $templateQuestion->type,
                'is_required' => $templateQuestion->is_required,
                'display_order' => $templateQuestion->display_order ?? ($questionIndex + 1),
            ]);

            foreach ($templateQuestion->options as $optionIndex => $templateOption) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $templateOption->option_text,
                    'option_value' => $templateOption->option_value,
                    'display_order' => $optionIndex + 1,
                ]);
            }
        }
    }

    private function buildQuestionStats($question, Survey $survey): array
    {
        if (in_array($question->type, ['scale', 'mcq'], true)) {
            $answers = Answer::where('question_id', $question->id)
                ->whereHas('response', function ($query) use ($survey) {
                    $query->where('survey_id', $survey->id);
                })
                ->get();

            $totalAnswers = $answers->count();
            $average = $answers->whereNotNull('answer_value')->avg('answer_value');

            $distribution = [];
            foreach ($question->options as $option) {
                $count = $answers->where('question_option_id', $option->id)->count();
                $distribution[] = [
                    'label' => $option->option_text,
                    'count' => $count,
                ];
            }

            return [
                'type' => $question->type,
                'total_answers' => $totalAnswers,
                'average' => $average,
                'distribution' => $distribution,
                'comments' => [],
            ];
        }

        if ($question->type === 'text') {
            $comments = Answer::where('question_id', $question->id)
                ->whereHas('response', function ($query) use ($survey) {
                    $query->where('survey_id', $survey->id);
                })
                ->whereNotNull('answer_text')
                ->pluck('answer_text')
                ->filter()
                ->values();

            return [
                'type' => 'text',
                'total_answers' => $comments->count(),
                'average' => null,
                'distribution' => [],
                'comments' => $comments,
            ];
        }

        return [
            'type' => $question->type,
            'total_answers' => 0,
            'average' => null,
            'distribution' => [],
            'comments' => [],
        ];
    }

    private function getAcademicDataForCurrentUser(): array
    {
        $user = auth()->user();

        if ($user->isPresidencyAdmin()) {

            $faculties = collect();
    
            $departments = collect();
    
            $courses = collect();
    
            $courseOfferings = collect();
    
        } else if ($user->isUniversityAdmin()) {
            $faculties = Faculty::orderBy('name_ar')->get();
            $departments = Department::with('faculty')->orderBy('name_ar')->get();
            $courses = Course::with('department.faculty')->orderBy('name_ar')->get();
            $courseOfferings = CourseOffering::with('course.department.faculty')->latest()->get();
        } elseif ($user->isFacultyAdmin()) {
            $faculties = Faculty::where('id', $user->faculty_id)->orderBy('name_ar')->get();
            $departments = Department::with('faculty')
                ->where('faculty_id', $user->faculty_id)
                ->orderBy('name_ar')
                ->get();
            $courses = Course::with('department.faculty')
                ->whereHas('department', function ($query) use ($user) {
                    $query->where('faculty_id', $user->faculty_id);
                })
                ->orderBy('name_ar')
                ->get();
            $courseOfferings = CourseOffering::with('course.department.faculty')
                ->whereHas('course.department', function ($query) use ($user) {
                    $query->where('faculty_id', $user->faculty_id);
                })
                ->latest()
                ->get();
        } elseif ($user->isDepartmentAdmin()) {
            $faculties = Faculty::where('id', $user->faculty_id)->orderBy('name_ar')->get();
            $departments = Department::with('faculty')
                ->where('id', $user->department_id)
                ->orderBy('name_ar')
                ->get();
            $courses = Course::with('department.faculty')
                ->where('department_id', $user->department_id)
                ->orderBy('name_ar')
                ->get();
            $courseOfferings = CourseOffering::with('course.department.faculty')
                ->whereHas('course', function ($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                })
                ->latest()
                ->get();
        } else {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        return [$faculties, $departments, $courses, $courseOfferings];
    }

    private function getTemplatesForCurrentUser()
    {
        $user = auth()->user();

        if ($user->isPresidencyAdmin()) {
            return collect();
        }

        if ($user->isUniversityAdmin()) {
            return SurveyTemplate::with(['faculty', 'department'])
                ->where('is_active', true)
                ->where('scope_level', 'university')
                ->orderBy('name')
                ->get();
        }

        if ($user->isFacultyAdmin()) {
            return SurveyTemplate::with(['faculty', 'department'])
                ->where('is_active', true)
                ->where('scope_level', 'faculty')
                ->where('faculty_id', $user->faculty_id)
                ->orderBy('name')
                ->get();
        }

        if ($user->isDepartmentAdmin()) {
            return SurveyTemplate::with(['faculty', 'department'])
                ->where('is_active', true)
                ->where(function ($query) use ($user) {
                    $query->where(function ($q) use ($user) {
                        $q->where('scope_level', 'department')
                            ->where('department_id', $user->department_id);
                    })->orWhere(function ($q) use ($user) {
                        $q->where('scope_level', 'course')
                            ->where('faculty_id', $user->faculty_id);
                    });
                })
                ->orderBy('name')
                ->get();
        }

        abort(403, 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
    }

    private function getVisibleSurveysQuery(User $user)
    {
        if ($user->isUniversityAdmin()) {
            return Survey::where('scope_level', 'university')
                ->where('survey_owner', Survey::OWNER_QUALITY_CENTER);
        }

        if ($user->isPresidencyAdmin()) {
            return Survey::where('scope_level', 'university')
                ->where('survey_owner', Survey::OWNER_PRESIDENCY);
        }

        if ($user->isFacultyAdmin()) {
            return Survey::where('faculty_id', $user->faculty_id)
                ->whereIn('scope_level', ['faculty', 'department']);
        }

        if ($user->isDepartmentAdmin()) {
            return Survey::where('department_id', $user->department_id)
                ->where('scope_level', 'department');
        }

        return Survey::whereHas('permissions', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('permission_type', 'view_results');
        });
    }

    private function resolveCourseOffering(array $validated, User $user): ?CourseOffering
    {
        if (($validated['survey_scope'] ?? null) !== 'course' || empty($validated['course_offering_id'])) {
            return null;
        }

        if (!$user->isDepartmentAdmin()) {
            abort(403, 'استبيانات المواد من صلاحية أدمن القسم فقط');
        }

        $courseOffering = CourseOffering::with('course.department.faculty')
            ->find($validated['course_offering_id']);

        if (!$courseOffering) {
            abort(404, 'المادة المسجلة غير موجودة');
        }

        $courseDepartmentId = $courseOffering->course?->department_id;

        if ((int) $courseDepartmentId !== (int) $user->department_id) {
            abort(403, 'لا يمكنك إنشاء استبيان لمادة خارج قسمك');
        }

        return $courseOffering;
    }

    private function resolveSurveyScope(array $validated, User $user, ?CourseOffering $courseOffering = null): array
    {
        if ($user->isUniversityAdmin() || $user->isPresidencyAdmin()) {
            return ['university', null, null];
        }

        if ($user->isFacultyAdmin()) {
            return ['faculty', $user->faculty_id, null];
        }

        if ($user->isDepartmentAdmin()) {
            if ($courseOffering) {
                return [
                    'department',
                    $courseOffering->course?->department?->faculty_id,
                    $courseOffering->course?->department_id,
                ];
            }

            return ['department', $user->faculty_id, $user->department_id];
        }

        abort(403, 'ليس لديك صلاحية إنشاء أو تعديل الاستبيانات');
    }

    private function canUseTemplate(SurveyTemplate $template, User $user, string $surveyScope = 'general'): bool
    {
        if ($user->isUniversityAdmin()) {
            return (bool) $template->is_active
                && $template->scope_level === 'university'
                && $surveyScope === 'general';
        }

        if ($user->isFacultyAdmin()) {
            return (bool) $template->is_active
                && $template->scope_level === 'faculty'
                && (int) $template->faculty_id === (int) $user->faculty_id
                && $surveyScope === 'general';
        }

        if ($user->isDepartmentAdmin()) {
            if ($surveyScope === 'course') {
                return (bool) $template->is_active
                    && $template->scope_level === 'course'
                    && (int) $template->faculty_id === (int) $user->faculty_id;
            }

            return (bool) $template->is_active
                && $template->scope_level === 'department'
                && (int) $template->department_id === (int) $user->department_id;
        }

        return false;
    }

    private function authorizeSurveyAccess(Survey $survey): void
    {
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            if (
                $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_QUALITY_CENTER
            ) {
                return;
            }
        
            abort(403, 'ليس لديك صلاحية الوصول إلى هذا الاستبيان');
        }
        
        if ($user->isPresidencyAdmin()) {
            if (
                $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_PRESIDENCY
            ) {
                return;
            }
        
            abort(403, 'ليس لديك صلاحية الوصول إلى هذا الاستبيان');
        }

        if ($user->isFacultyAdmin()) {

            if ((int) $survey->faculty_id === (int) $user->faculty_id) {

                return;
            }

            abort(403, 'ليس لديك صلاحية الوصول إلى هذا الاستبيان');
        }

        if ($user->isDepartmentAdmin()) {
            if ($survey->scope_level === 'department' && (int) $survey->department_id === (int) $user->department_id) {
                return;
            }

            abort(403, 'ليس لديك صلاحية الوصول إلى هذا الاستبيان');
        }

        $hasPermission = $survey->permissions()
            ->where('user_id', $user->id)
            ->where('permission_type', 'view_results')
            ->exists();

        if (!$hasPermission) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذا الاستبيان');
        }
    }

    private function authorizeSurveyManagement(Survey $survey): void
    {
        $user = auth()->user();

        if ($user->isUniversityAdmin()) {
            if (
                $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_QUALITY_CENTER
            ) {
                return;
            }
        
            abort(403, 'ليس لديك صلاحية تعديل هذا الاستبيان');
        }
        
        if ($user->isPresidencyAdmin()) {
            if (
                $survey->scope_level === 'university'
                && $survey->survey_owner === Survey::OWNER_PRESIDENCY
            ) {
                return;
            }
        
            abort(403, 'ليس لديك صلاحية تعديل هذا الاستبيان');
        }

        if ($user->isFacultyAdmin()) {
            if ($survey->scope_level === 'faculty' && (int) $survey->faculty_id === (int) $user->faculty_id) {
                return;
            }

            abort(403, 'ليس لديك صلاحية تعديل هذا الاستبيان');
        }

        if ($user->isDepartmentAdmin()) {
            if ($survey->scope_level === 'department' && (int) $survey->department_id === (int) $user->department_id) {
                return;
            }

            abort(403, 'ليس لديك صلاحية تعديل هذا الاستبيان');
        }

        abort(403, 'ليس لديك صلاحية تعديل هذا الاستبيان');
    }

    private function authorizeSurveyResultsAccess(Survey $survey): void
    {
        $user = auth()->user();

        if (
            $user->isUniversityAdmin()
            || $user->isPresidencyAdmin()
            || $user->isFacultyAdmin()
            || $user->isDepartmentAdmin()
        ) {
            $this->authorizeSurveyAccess($survey);
            return;
        }

        $hasPermission = $survey->permissions()
            ->where('user_id', $user->id)
            ->where('permission_type', 'view_results')
            ->exists();

        if (!$hasPermission) {
            abort(403, 'ليس لديك صلاحية الوصول إلى نتائج هذا الاستبيان');
        }
    }
    public function bulkCreate()
    {
        $user = auth()->user();

        if (!$user->isDepartmentAdmin()) {
            abort(403, 'الإنشاء الجماعي لاستبيانات المقررات متاح لأدمن القسم فقط');
        }

        $templates = SurveyTemplate::with(['faculty', 'department'])
            ->where('is_active', true)
            ->where('scope_level', 'course')
            ->where('faculty_id', $user->faculty_id)
            ->orderBy('name')
            ->get();

        $offerings = CourseOffering::with('course.department.faculty')
            ->whereHas('course', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->latest()
            ->get();

        $academicYears = $offerings->pluck('academic_year')->filter()->unique()->values();
        $semesters = [
            'first' => 'الفصل الدراسي الأول',
            'second' => 'الفصل الدراسي الثاني',
            'summer' => 'الفصل الصيفي',
        ];

        return view('admin.surveys.bulk-create', compact(
            'templates',
            'offerings',
            'academicYears',
            'semesters'
        ));
    }
    public function bulkStore(Request $request)
    {
        $user = auth()->user();


        if (!$user->isDepartmentAdmin()) {
            abort(403, 'الإنشاء الجماعي لاستبيانات المقررات متاح لأدمن القسم فقط');
        }

        $request->merge([
            'auto_close_on_limit' => $request->has('auto_close_on_limit'),
        ]);

        $validated = $request->validate([
            'template_id' => 'required|exists:survey_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year' => 'required|string|max:255',
            'semester' => 'required|in:first,second,summer',
            'expected_responses' => 'nullable|integer|min:1',
            'auto_close_on_limit' => 'nullable|boolean',
        ]);

        $template = SurveyTemplate::with(['sections.questions.options', 'questions.options'])
            ->findOrFail($validated['template_id']);

        if ($template->scope_level !== 'course') {
            return back()->withErrors([
                'template_id' => 'يجب اختيار قالب خاص بالمقررات.',
            ])->withInput();
        }

        if ((int) $template->faculty_id !== (int) $user->faculty_id) {
            return back()->withErrors([
                'template_id' => 'هذا القالب لا يتبع نفس كلية المستخدم الحالي.',
            ])->withInput();
        }

        $offerings = CourseOffering::with('course.department.faculty')
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester'])
            ->whereHas('course', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->get();

        if ($offerings->isEmpty()) {
            return back()->withErrors([
                'academic_year' => 'لا توجد مواد مسجلة مطابقة لهذا العام الدراسي والفصل داخل القسم.',
            ])->withInput();
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($offerings, $validated, $template, $request, &$created, &$skipped) {
            foreach ($offerings as $offering) {
                $alreadyExists = Survey::where('course_offering_id', $offering->id)
                    ->where('title', $validated['title'])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                $survey = Survey::create([
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? null,

                    'scope_level' => 'department',
                    'faculty_id' => $offering->course?->department?->faculty_id,
                    'department_id' => $offering->course?->department_id,

                    'course_offering_id' => $offering->id,
                    'course_title' => $offering->course?->name_ar,
                    'department_name' => $offering->course?->department?->name_ar,

                    'semester' => $offering->semester,
                    'level' => $offering->level,
                    'academic_year' => $offering->academic_year,

                    'is_active' => $request->has('is_active'),
                    'allow_multiple_submissions' => $request->has('allow_multiple_submissions'),
                    'expected_responses' => $validated['expected_responses'] ?? null,
                    'auto_close_on_limit' => $request->has('auto_close_on_limit'),
                ]);

                $this->copyTemplateToSurvey($template, $survey);
                $created++;
            }
        });

        return redirect()->route('admin.surveys.index')
            ->with('success', "تم إنشاء {$created} استبيان/استبيانات بنجاح، وتم تخطي {$skipped} لوجودها مسبقًا.");
    }
    public function exportActiveSurveys()
    {
        $user = auth()->user();

        if (
            !$user->isUniversityAdmin() &&
            !$user->isPresidencyAdmin() &&
            !$user->isFacultyAdmin() &&
            !$user->isDepartmentAdmin()
        ) {
            abort(403, 'ليس لديك صلاحية تصدير الاستبيانات');
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new ActiveSurveysExport($user),
            'active-surveys-' . now()->format('Y-m-d-H-i') . '.xlsx'
        );
    }
}
