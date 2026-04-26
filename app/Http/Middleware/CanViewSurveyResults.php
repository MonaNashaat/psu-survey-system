<?php

namespace App\Http\Middleware;

use App\Models\Survey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanViewSurveyResults
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $survey = $request->route('survey');

        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$survey instanceof Survey) {
            $survey = Survey::findOrFail($survey);
        }

        $hasPermission = $survey->permissions()
            ->where('user_id', $user->id)
            ->where('permission_type', 'view_results')
            ->exists();

        if (!$hasPermission) {
            abort(403, 'ليس لديك صلاحية لعرض نتائج هذا الاستبيان');
        }

        return $next($request);
    }
}