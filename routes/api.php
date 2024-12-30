<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\GradeOneController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\FrontendNewsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// تكوين Rate Limiting
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Authentication Routes (Main Database Only)
Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.auth.logout');

// Protected Routes (Admin Panel)
Route::middleware(['auth:sanctum', 'api'])->group(function () {
    // Dashboard Routes
    Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function () {
        // Dashboard Index
        Route::get('/', [App\Http\Controllers\Api\DashboardController::class, 'index'])->name('api.dashboard.index');

        // User Management Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\UserController::class, 'index'])->name('api.dashboard.users.index');
            Route::post('/', [App\Http\Controllers\Api\UserController::class, 'store'])->name('api.dashboard.users.store');
            Route::get('/{id}', [App\Http\Controllers\Api\UserController::class, 'show'])->name('api.dashboard.users.show');
            Route::put('/{id}', [App\Http\Controllers\Api\UserController::class, 'update'])->name('api.dashboard.users.update');
            Route::delete('/{id}', [App\Http\Controllers\Api\UserController::class, 'destroy'])->name('api.dashboard.users.destroy');
            Route::put('/{id}/permissions-roles', [App\Http\Controllers\Api\UserController::class, 'updatePermissionsRoles'])
                ->name('api.dashboard.users.permissions-roles.update');
            Route::post('/{id}/update-profile-photo', [App\Http\Controllers\Api\UserController::class, 'updateProfilePhoto'])
                ->name('api.dashboard.users.update-profile-photo');
        });

        // Notifications Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\NotificationController::class, 'index'])
                ->name('api.dashboard.notifications.index');
            Route::post('/mark-as-read/{id}', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])
                ->name('api.dashboard.notifications.mark-as-read');
            Route::post('/mark-all-as-read', [App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead'])
                ->name('api.dashboard.notifications.mark-all-as-read');
            Route::post('/delete-selected', [App\Http\Controllers\Api\NotificationController::class, 'deleteSelected'])
                ->name('api.dashboard.notifications.delete-selected');
            Route::post('/handle-actions', [App\Http\Controllers\Api\NotificationController::class, 'handleActions'])
                ->name('api.dashboard.notifications.handle-actions');
            Route::delete('/{id}', [App\Http\Controllers\Api\NotificationController::class, 'delete'])
                ->name('api.dashboard.notifications.delete');
        });

        // Messages Routes
        Route::prefix('messages')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\MessageController::class, 'index'])
                ->name('api.dashboard.messages.index');
            Route::post('/', [App\Http\Controllers\Api\MessageController::class, 'store'])
                ->name('api.dashboard.messages.store');
            Route::get('/sent', [App\Http\Controllers\Api\MessageController::class, 'sent'])
                ->name('api.dashboard.messages.sent');
            Route::get('/{id}', [App\Http\Controllers\Api\MessageController::class, 'show'])
                ->name('api.dashboard.messages.show');
            Route::post('/{id}/reply', [App\Http\Controllers\Api\MessageController::class, 'reply'])
                ->name('api.dashboard.messages.reply');
            Route::post('/{id}/mark-as-read', [App\Http\Controllers\Api\MessageController::class, 'markAsRead'])
                ->name('api.dashboard.messages.mark-as-read');
            Route::post('/{id}/toggle-important', [App\Http\Controllers\Api\MessageController::class, 'toggleImportant'])
                ->name('api.dashboard.messages.toggle-important');
            Route::delete('/{id}', [App\Http\Controllers\Api\MessageController::class, 'delete'])
                ->name('api.dashboard.messages.delete');
            Route::post('/delete-selected', [App\Http\Controllers\Api\MessageController::class, 'deleteSelected'])
                ->name('api.dashboard.messages.delete-selected');
        });

        // News Management Routes
        Route::apiResource('news', App\Http\Controllers\Api\NewsController::class)->names([
            'index' => 'api.dashboard.news.index',
            'store' => 'api.dashboard.news.store',
            'show' => 'api.dashboard.news.show',
            'update' => 'api.dashboard.news.update',
            'destroy' => 'api.dashboard.news.destroy'
        ]);
  

        // Dashboard Resources
        Route::apiResource('school-classes', SchoolClassController::class)->names([
            'index' => 'api.admin.school-classes.index',
            'store' => 'api.admin.school-classes.store',
            'show' => 'api.admin.school-classes.show',
            'update' => 'api.admin.school-classes.update',
            'destroy' => 'api.admin.school-classes.destroy',
        ]);

        Route::apiResource('subjects', SubjectController::class)->names([
            'index' => 'api.admin.subjects.index',
            'store' => 'api.admin.subjects.store',
            'show' => 'api.admin.subjects.show',
            'update' => 'api.admin.subjects.update',
            'destroy' => 'api.admin.subjects.destroy',
        ]);

        Route::apiResource('semesters', SemesterController::class)->names([
            'index' => 'api.admin.semesters.index',
            'store' => 'api.admin.semesters.store',
            'show' => 'api.admin.semesters.show',
            'update' => 'api.admin.semesters.update',
            'destroy' => 'api.admin.semesters.destroy',
        ]);

        Route::apiResource('articles', ArticleController::class)->names([
            'index' => 'api.admin.articles.index',
            'store' => 'api.admin.articles.store',
            'show' => 'api.admin.articles.show',
            'update' => 'api.admin.articles.update',
            'destroy' => 'api.admin.articles.destroy',
        ]);

        Route::apiResource('files', FileController::class)->names([
            'index' => 'api.admin.files.index',
            'store' => 'api.admin.files.store',
            'show' => 'api.admin.files.show',
            'update' => 'api.admin.files.update',
            'destroy' => 'api.admin.files.destroy',
        ]);

        Route::apiResource('categories', CategoryController::class)->names([
            'index' => 'api.admin.categories.index',
            'store' => 'api.admin.categories.store',
            'show' => 'api.admin.categories.show',
            'update' => 'api.admin.categories.update',
            'destroy' => 'api.admin.categories.destroy',
        ]);

        // Calendar
        Route::prefix('calendar')->group(function () {
            Route::get('/{month?}/{year?}', [CalendarController::class, 'calendar'])->name('api.admin.calendar.show');
            Route::post('/event', [CalendarController::class, 'store'])->name('api.admin.calendar.store');
            Route::put('/event/{id}', [CalendarController::class, 'update'])->name('api.admin.calendar.update');
            Route::delete('/event/{id}', [CalendarController::class, 'destroy'])->name('api.admin.calendar.destroy');
        });


        // Analytics & Performance
        Route::prefix('analytics')->group(function () {
            Route::get('/visitors', [AnalyticsController::class, 'visitors'])->name('api.admin.analytics.visitors');
            Route::get('/performance', [PerformanceController::class, 'index'])->name('api.admin.performance.index');
        });

        // Filters
        Route::prefix('filter')->group(function () {
            Route::get('/files', [FilterController::class, 'index'])->name('api.filter.files');
            Route::get('/subjects/{classId}', [FilterController::class, 'getSubjectsByClass'])->name('api.filter.subjects');
            Route::get('/semesters/{subjectId}', [FilterController::class, 'getSemestersBySubject'])->name('api.filter.semesters');
            Route::get('/files/{semesterId}', [FilterController::class, 'getFileTypesBySemester'])->name('api.filter.file-types');
        });

    });
});

 // Comments Routes with throttle middleware
Route::middleware(['api', 'throttle:60,1'])->prefix('{database}')->group(function () {
    // مسارات التعليقات للأخبار
    Route::get('/news/{id}/comments', [App\Http\Controllers\Api\CommentController::class, 'index'])->name('api.comments.news.index');
    Route::post('/news/{id}/comments', [App\Http\Controllers\Api\CommentController::class, 'store'])->middleware('auth:sanctum')->name('api.comments.news.store');
    
    // مسارات التعليقات للمقالات
    Route::get('/lesson/articles/{id}/comments', [App\Http\Controllers\Api\CommentController::class, 'index'])->name('api.comments.articles.index');
    Route::post('/lesson/articles/{id}/comments', [App\Http\Controllers\Api\CommentController::class, 'store'])->middleware('auth:sanctum')->name('api.comments.articles.store');
});

// Public Content Routes
Route::middleware(['api', 'throttle:api'])->group(function () {
    Route::prefix('{database}')->group(function () {
        Route::prefix('lesson')->group(function () {
            Route::get('/', [GradeOneController::class, 'index']);
            Route::get('/{id}', [GradeOneController::class, 'show']);
            Route::get('/subjects/{subject}', [GradeOneController::class, 'showSubject']);
            Route::get('/subjects/{subject}/articles/{semester}/{category}', [GradeOneController::class, 'subjectArticles']);
            Route::get('/articles/{article}', [GradeOneController::class, 'showArticle']);
            Route::get('/files/download/{id}', [GradeOneController::class, 'downloadFile']);
        });

        // Frontend News Routes (Public)
        Route::prefix('news')->group(function () {
            Route::get('/', [FrontendNewsController::class, 'index'])->name('api.frontend.news.index');
            Route::get('/{id}', [FrontendNewsController::class, 'show'])->name('api.frontend.news.show');
            Route::get('/category/{categorySlug}', [FrontendNewsController::class, 'category'])->name('api.frontend.news.category');
        });

        // Keywords & Categories
        Route::get('/keywords', [KeywordController::class, 'index'])->name('api.frontend.keywords.index');
        Route::get('/keywords/{keywords}', [KeywordController::class, 'indexByKeyword'])->name('api.frontend.keywords.by-keyword');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.frontend.categories.show');

        // File Downloads
        Route::get('/files/download/{id}', [FileController::class, 'downloadFile'])->name('api.frontend.files.download');
        Route::get('/download/{file}', [FileController::class, 'showDownloadPage'])->name('api.frontend.files.show-download');
        Route::get('/download-wait/{file}', [FileController::class, 'processDownload'])->name('api.frontend.files.process-download');
    });
});

// Additional Subject Routes
Route::get('/subjects/by-grade/{grade_level}', [SubjectController::class, 'indexByGrade'])->name('api.subjects.by-grade');
Route::get('/classes-by-country/{country}', [SubjectController::class, 'getClassesByCountry'])->name('api.classes-by-country');