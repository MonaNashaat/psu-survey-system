<?php

use App\Http\Controllers\Admin\AcademicStructureController;
use App\Http\Controllers\Admin\SurveyAdminController;
use App\Http\Controllers\Admin\SurveyTemplateController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Public Survey Routes
|--------------------------------------------------------------------------
*/

Route::get('/surveys/{id}', [SurveyController::class, 'show'])->name('surveys.show');
Route::post('/surveys/{id}/submit', [SurveyController::class, 'submit'])->name('surveys.submit');
Route::get('/survey-thank-you', [SurveyController::class, 'thankyou'])->name('surveys.thankyou');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [SurveyAdminController::class, 'dashboard'])->name('home');
    Route::get('/dashboard', [SurveyAdminController::class, 'dashboard'])->name('dashboard');

    Route::get(
        '/surveys/{survey}/analysis/export',
        [SurveyAdminController::class, 'exportAnalysis']
    )->name('surveys.analysis.export');
    
    /*
    |--------------------------------------------------------------------------
    | Survey Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/surveys', [SurveyAdminController::class, 'index'])->name('surveys.index');

    Route::middleware(['role:university_admin,presidency_admin,faculty_admin,department_admin'])->group(function () {        // static routes first
        Route::get('/surveys/create', [SurveyAdminController::class, 'create'])->name('surveys.create');
        Route::post('/surveys', [SurveyAdminController::class, 'store'])->name('surveys.store');

        Route::get('/surveys/bulk-create', [SurveyAdminController::class, 'bulkCreate'])->name('surveys.bulk.create');
        Route::post('/surveys/bulk-create', [SurveyAdminController::class, 'bulkStore'])->name('surveys.bulk.store');

        Route::get('/surveys/export/active', [SurveyAdminController::class, 'exportActiveSurveys'])
            ->name('surveys.export.active');

        // dynamic routes after static routes
        Route::get('/surveys/{survey}', [SurveyAdminController::class, 'show'])->name('surveys.show');
        Route::get('/surveys/{survey}/edit', [SurveyAdminController::class, 'edit'])->name('surveys.edit');
        Route::put('/surveys/{survey}', [SurveyAdminController::class, 'update'])->name('surveys.update');
        Route::delete('/surveys/{survey}', [SurveyAdminController::class, 'destroy'])->name('surveys.destroy');

        Route::get('/surveys/{survey}/permissions', [SurveyAdminController::class, 'permissions'])->name('surveys.permissions');
        Route::post('/surveys/{survey}/permissions', [SurveyAdminController::class, 'storePermission'])->name('surveys.permissions.store');
        Route::delete('/surveys/{survey}/permissions/{permission}', [SurveyAdminController::class, 'destroyPermission'])->name('surveys.permissions.destroy');
    });

    Route::middleware(['survey.results.access'])->group(function () {
        Route::get('/surveys/{survey}/results', [SurveyAdminController::class, 'results'])->name('surveys.results');
        Route::get('/surveys/{survey}/export/excel', [SurveyAdminController::class, 'exportExcel'])->name('surveys.export.excel');
        Route::get('/surveys/{survey}/export/pdf', [SurveyAdminController::class, 'exportPdf'])->name('surveys.export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | Template Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:university_admin,faculty_admin,department_admin'])->prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [SurveyTemplateController::class, 'index'])->name('index');
        Route::get('/create', [SurveyTemplateController::class, 'create'])->name('create');
        Route::post('/', [SurveyTemplateController::class, 'store'])->name('store');
        Route::get('/{template}/edit', [SurveyTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [SurveyTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [SurveyTemplateController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Academic Structure Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:university_admin,faculty_admin,department_admin'])->prefix('academic')->name('academic.')->group(function () {
        Route::get('/faculties', [AcademicStructureController::class, 'facultiesIndex'])->name('faculties.index');
        Route::post('/faculties', [AcademicStructureController::class, 'facultiesStore'])->name('faculties.store');
        Route::get('/faculties/{faculty}/edit', [AcademicStructureController::class, 'facultiesEdit'])->name('faculties.edit');
        Route::put('/faculties/{faculty}', [AcademicStructureController::class, 'facultiesUpdate'])->name('faculties.update');
        Route::delete('/faculties/{faculty}', [AcademicStructureController::class, 'facultiesDestroy'])->name('faculties.destroy');

        Route::get('/departments', [AcademicStructureController::class, 'departmentsIndex'])->name('departments.index');
        Route::post('/departments', [AcademicStructureController::class, 'departmentsStore'])->name('departments.store');
        Route::get('/departments/{department}/edit', [AcademicStructureController::class, 'departmentsEdit'])->name('departments.edit');
        Route::put('/departments/{department}', [AcademicStructureController::class, 'departmentsUpdate'])->name('departments.update');
        Route::delete('/departments/{department}', [AcademicStructureController::class, 'departmentsDestroy'])->name('departments.destroy');

        Route::get('/courses', [AcademicStructureController::class, 'coursesIndex'])->name('courses.index');
        Route::post('/courses', [AcademicStructureController::class, 'coursesStore'])->name('courses.store');
        Route::post('/courses/import', [AcademicStructureController::class, 'coursesImport'])->name('courses.import');
        Route::get('/courses/{course}/edit', [AcademicStructureController::class, 'coursesEdit'])->name('courses.edit');
        Route::put('/courses/{course}', [AcademicStructureController::class, 'coursesUpdate'])->name('courses.update');
        Route::delete('/courses/{course}', [AcademicStructureController::class, 'coursesDestroy'])->name('courses.destroy');

        Route::get('/offerings', [AcademicStructureController::class, 'offeringsIndex'])->name('offerings.index');
        Route::post('/offerings', [AcademicStructureController::class, 'offeringsStore'])->name('offerings.store');
        Route::post('/offerings/import', [AcademicStructureController::class, 'offeringsImport'])->name('offerings.import');
        Route::get('/offerings/{offering}/edit', [AcademicStructureController::class, 'offeringsEdit'])->name('offerings.edit');
        Route::put('/offerings/{offering}', [AcademicStructureController::class, 'offeringsUpdate'])->name('offerings.update');
        Route::delete('/offerings/{offering}', [AcademicStructureController::class, 'offeringsDestroy'])->name('offerings.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | User Management Routes (University Admin Only)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:university_admin'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/auth.php';