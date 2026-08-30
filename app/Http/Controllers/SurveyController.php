<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function show(Request $request, $id)
    {
        $survey = Survey::with([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions.options',
        ])->findOrFail($id);

        if (!$survey->isAvailableForSubmission()) {
            return view('surveys.closed', compact('survey'));
        }

        $cookieName = 'survey_' . $survey->id . '_submitted';
        $alreadySubmittedByCookie = $request->cookie($cookieName) ? true : false;

        return view('surveys.show', compact('survey', 'alreadySubmittedByCookie'));
    }

    public function submit(Request $request, $id)
    {
        $survey = Survey::with([
            'faculty',
            'department',
            'courseOffering.course.department.faculty',
            'sections.questions.options',
            'questions.options',
        ])->findOrFail($id);

        if (!$survey->isAvailableForSubmission()) {
            return redirect()
                ->route('surveys.show', $survey->id)
                ->with('survey_closed', 'تم إغلاق هذا الاستبيان، ولم يعد متاحًا لاستقبال ردود جديدة.');
        }

        $rules = [];

        foreach ($survey->sections as $section) {
            foreach ($section->questions as $question) {
                $key = 'answers.' . $question->id;
                if ($question->type === 'text') {
                    $rules[$key] = $question->is_required
                        ? 'required|string'
                        : 'nullable|string';
                } elseif ($question->type === 'date') {
                    $rules[$key] = $question->is_required
                        ? 'required|date'
                        : 'nullable|date';
                } else {
                    $rules[$key] = $question->is_required
                        ? 'required'
                        : 'nullable';
                }
            }
        }

        foreach ($survey->questions->whereNull('survey_section_id') as $question) {
            $key = 'answers.' . $question->id;
            if ($question->type === 'text') {
                $rules[$key] = $question->is_required
                    ? 'required|string'
                    : 'nullable|string';
            } elseif ($question->type === 'date') {
                $rules[$key] = $question->is_required
                    ? 'required|date'
                    : 'nullable|date';
            } else {
                $rules[$key] = $question->is_required
                    ? 'required'
                    : 'nullable';
            }
        }

        $validated = $request->validate($rules);

        $deviceHash = $this->generateDeviceHash($request, $survey->id);

        if (!$survey->allow_multiple_submissions) {
            $cookieName = 'survey_' . $survey->id . '_submitted';

            $alreadySubmitted = SurveyResponse::where('survey_id', $survey->id)
                ->where(function ($query) use ($request, $deviceHash) {
                    $query->where('device_hash', $deviceHash);

                    if ($request->ip()) {
                        $query->orWhere('ip_address', $request->ip());
                    }
                })
                ->exists();

            if ($alreadySubmitted || $request->cookie($cookieName)) {
                return back()
                    ->withInput()
                    ->with('duplicate_error', 'تم إرسال هذا الاستبيان من هذا الجهاز من قبل.');
            }
        }

        // تحقق إضافي لحظة الحفظ لمنع السباق بين أكثر من إرسال
        if (
            $survey->auto_close_on_limit &&
            !empty($survey->expected_responses) &&
            $survey->responses()->count() >= $survey->expected_responses
        ) {
            $survey->update(['is_active' => false]);

            return redirect()
                ->route('surveys.show', $survey->id)
                ->with('survey_closed', 'تم إغلاق الاستبيان بعد الوصول إلى العدد المطلوب من الردود.');
        }

        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'response_token' => Str::uuid(),
            'ip_address' => $request->ip(),
            'device_hash' => $deviceHash,
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        foreach ($survey->sections as $section) {
            foreach ($section->questions as $question) {
                $this->storeAnswer($question, $validated, $response->id);
            }
        }

        foreach ($survey->questions->whereNull('survey_section_id') as $question) {
            $this->storeAnswer($question, $validated, $response->id);
        }

        // بعد الحفظ: لو وصل للعدد المطلوب، نقفل الاستبيان تلقائيًا
        if (
            $survey->auto_close_on_limit &&
            !empty($survey->expected_responses) &&
            $survey->responses()->count() >= $survey->expected_responses
        ) {
            $survey->update(['is_active' => false]);
        }

        $cookieName = 'survey_' . $survey->id . '_submitted';

        return redirect()
            ->route('surveys.thankyou')
            ->cookie($cookieName, '1', 60 * 24 * 365);
    }

    public function thankyou()
    {
        return view('surveys.thankyou');
    }

    private function storeAnswer($question, array $validated, int $responseId): void
    {
        $value = $validated['answers'][$question->id] ?? null;

        if (is_null($value) || $value === '') {
            return;
        }

        if ($question->type === 'text') {
            Answer::create([
                'survey_response_id' => $responseId,
                'question_id' => $question->id,
                'answer_text' => $value,
            ]);

            return;
        }

        $selectedOption = $question->options->firstWhere('id', (int) $value);

        Answer::create([
            'survey_response_id' => $responseId,
            'question_id' => $question->id,
            'question_option_id' => $selectedOption?->id,
            'answer_value' => $selectedOption?->option_value,
        ]);
    }

    private function generateDeviceHash(Request $request, int $surveyId): string
    {
        return hash('sha256', implode('|', [
            $surveyId,
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
        ]));
    }
}