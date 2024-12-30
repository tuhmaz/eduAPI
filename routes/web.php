<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Str;
use App\Http\Controllers\{
    language\LanguageController,
    authentications\LoginBasic,
    authentications\RegisterBasic,
    RoleController,
    PermissionController,
    UserController,
    NotificationController,
    SchoolClassController,
    SubjectController,
    SemesterController,
    ArticleController,
    FileController,
    NewsController,
    FrontendNewsController,
    DashboardController,
    MessageController,
    SettingsController,
    GradeOneController,
    HomeController,
    ImageUploadController,
    CommentController,
    ReactionController,
    FilterController,
    KeywordController,
    CategoryController,
    CalendarController,
    SitemapController,
    Auth\SocialAuthController,
    AnalyticsController,
    PerformanceController,
    TestRedisController,
    Auth\VerifyEmailController,
    Auth\SMTPTestController,
    SecurityLogController,
    TrustedIpController,
    MonitoringController,
    BlockedIpsController,
    Auth\LogoutController,
    pages\MiscError
};

// === Authentication Routes ===
Route::prefix('auth')->group(function () {
    Route::post('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('auth.logout')->middleware('auth');

    Route::get('/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
});

// === Home Routes ===
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/set-database', [HomeController::class, 'setDatabase'])->name('setDatabase');
Route::get('/lang/{locale}', [LanguageController::class, 'swap'])->name('dashboard.lang-swap');

// === Static Pages ===
Route::get('/terms-of-service', function () {
    $terms = File::get(resource_path('markdown/terms.md'));
    return view('terms', ['terms' => Str::markdown($terms)]);
})->name('terms.show');

Route::get('/privacy-policy', function () {
    $policy = File::get(resource_path('markdown/policy.md'));
    return view('policy', ['policy' => Str::markdown($policy)]);
})->name('privacy-policy.show');

// === Upload Routes ===
Route::prefix('upload')->group(function () {
    Route::post('/image', [ImageUploadController::class, 'upload'])->name('upload.image');
    Route::post('/file', [ImageUploadController::class, 'uploadFile'])->name('upload.file');
});

// === Email Verification Routes ===
Route::get('/email/verify', [VerifyEmailController::class, 'show'])->middleware(['auth'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [VerifyEmailController::class, 'send'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// === Dashboard Routes ===
Route::middleware(['auth:sanctum', config('jetstream.auth_session'),'verified'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // === Sitemap Routes ===
    Route::prefix('sitemap')->middleware('can:manage sitemap')->group(function () {
        Route::get('/', [SitemapController::class, 'index'])->name('sitemap.index');
        Route::get('/manage', [SitemapController::class, 'manageIndex'])->name('sitemap.manage');
        Route::get('/generate', [SitemapController::class, 'generate'])->name('sitemap.generate');
        Route::get('/generate-articles', [SitemapController::class, 'generateArticlesSitemap'])->name('sitemap.generate.articles');
        Route::get('/generate-news', [SitemapController::class, 'generateNewsSitemap'])->name('sitemap.generate.news');
        Route::get('sitemap/generate-static', [SitemapController::class, 'generateStaticSitemap'])->name('sitemap.generate.static');
        Route::post('/update', [SitemapController::class, 'updateResourceInclusion'])->name('sitemap.updateResourceInclusion');
        Route::delete('/delete/{type}/{database}', [SitemapController::class, 'delete'])->name('sitemap.delete');
    });

    // === Calendar Routes ===
    Route::prefix('calendar')->middleware('can:manage calendar')->group(function () {
        Route::get('{month?}/{year?}', [CalendarController::class, 'calendar'])->name('calendar.index');
        Route::post('event', [CalendarController::class, 'store'])->name('events.store');
        Route::put('event/{event}', [CalendarController::class, 'update'])->name('events.update');
        Route::delete('event/{event}', [CalendarController::class, 'destroy'])->name('events.destroy');
    });

    // === Classes, Subjects, and Semesters ===
    Route::resource('classes', SchoolClassController::class)->middleware('can:manage classes');
    Route::resource('subjects', SubjectController::class)->middleware('can:manage subjects');
    Route::resource('semesters', SemesterController::class)->middleware('can:manage semesters');

    // === Articles Routes ===
    Route::resource('articles', ArticleController::class)->middleware('can:manage articles');
    Route::get('articles/class/{grade_level}', [ArticleController::class, 'indexByClass'])->name('articles.forClass');

    // === Files Routes ===
    Route::resource('files', FileController::class);

    // === News Routes ===
    Route::resource('news', NewsController::class)->middleware('can:manage news');

    // === Categories ===
    Route::resource('categories', CategoryController::class)->middleware('can:manage categories');
    
    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('can:manage settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('can:manage settings');
    Route::post('/settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.test.email')->middleware('can:manage settings');

    // Error page route
    Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('dashboard.pages-misc-error');

     // Role & Permission Management routes
     Route::resource('roles', RoleController::class)->middleware(['can:manage roles']);
     Route::resource('permissions', PermissionController::class)->middleware(['can:manage permissions']);


    // Notifications routes
    Route::resource('notifications', NotificationController::class)->only(['index', 'destroy']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/handle-actions', [NotificationController::class, 'handleActions'])->name('notifications.handleActions');
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'delete'])->name('notifications.delete');
    Route::patch('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

    // Comments & Reactions routes
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/reactions', [ReactionController::class, 'store'])->name('reactions.store');


    // === Messages ===
    Route::prefix('messages')->group(function () {
        Route::get('compose', [MessageController::class, 'compose'])->name('messages.compose');
        Route::post('send', [MessageController::class, 'send'])->name('messages.send');
        Route::get('/', [MessageController::class, 'index'])->name('messages.index');
        Route::get('sent', [MessageController::class, 'sent'])->name('messages.sent');
        Route::get('received', [MessageController::class, 'received'])->name('messages.received');
        Route::get('important', [MessageController::class, 'important'])->name('messages.important');
        Route::get('drafts', [MessageController::class, 'drafts'])->name('messages.drafts');
        Route::get('trash', [MessageController::class, 'trash'])->name('messages.trash');
        Route::delete('trash', [MessageController::class, 'deleteTrash'])->name('messages.deleteTrash');
        Route::delete('{id}', [MessageController::class, 'delete'])->name('messages.delete');
        Route::get('{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');
        Route::post('/{id}/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');
        Route::post('{id}/toggle-important', [MessageController::class, 'toggleImportant'])->name('messages.toggleImportant');
   });

    // Users routes
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('can:manage users');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::match(['put', 'post'], 'users/{user}', [UserController::class, 'update'])
        ->name('users.update')
        ->middleware('web');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('can:manage users');

    // User Profile Photo Route
    Route::post('users/{user}/update-photo', [UserController::class, 'updateProfilePhoto'])
        ->name('users.update-photo')
        ->middleware(['web', 'auth']);

    // User Permissions & Roles Routes
    Route::get('users/{user}/permissions-roles', [UserController::class, 'permissions_roles'])
        ->name('users.permissions_roles')
        ->middleware('can:manage permissions');
    Route::put('users/{user}/permissions-roles', [UserController::class, 'updatePermissionsRoles'])
        ->name('users.updatePermissionsRoles')
        ->middleware('can:manage permissions');


    // === Performance Monitoring ===
    Route::prefix('performance')->middleware(['cache.headers:public;max_age=60'])->group(function () {
        Route::get('/', [PerformanceController::class, 'dashboard'])->name('performance.dashboard');
    });

    // === Monitoring Routes ===
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('web.monitoring.index');
        Route::get('/monitoring/stats', [MonitoringController::class, 'getStats'])->name('web.monitoring.stats');
        Route::post('/monitoring/clear-cache', [MonitoringController::class, 'clearCache'])->name('web.monitoring.clear-cache');
    });

    // === Redis Routes ===
    Route::get('/test-redis', [TestRedisController::class, 'testRedis'])->middleware('can:manage redis');
    Route::get('/clear-cache', [TestRedisController::class, 'clearCache'])->middleware('can:manage redis');

    // === SMTP Test Routes ===
    Route::middleware(['web'])->group(function () {
        Route::get('/smtp/test-page', function () {
            return view('smtp-test');
        })->name('smtp.test-page')->middleware('can:manage test');
        Route::get('/smtp/test', [SettingsController::class, 'testSMTPConnection'])->name('smtp.test')->middleware('can:manage test');
        Route::post('/smtp/send-test', [SettingsController::class, 'sendTestEmail'])->name('smtp.send-test')->middleware('can:manage test');
    });

    // === Security and IP Management ===
    Route::prefix('security')->name('security.')->middleware('can:manage security')->group(function () {
        Route::get('/logs', [SecurityLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/filter', [SecurityLogController::class, 'filter'])->name('logs.filter');
        Route::get('/logs/export', [SecurityLogController::class, 'export'])->name('logs.export');
        Route::get('/logs/{log}', [SecurityLogController::class, 'show'])->name('logs.show');
        Route::post('logs/{log}/resolve', [SecurityLogController::class, 'resolve'])->name('logs.resolve');
        Route::post('logs/{log}/block-ip', [SecurityLogController::class, 'blockIp'])->name('logs.block-ip');
        Route::post('logs/{log}/mark-trusted', [SecurityLogController::class, 'markTrusted'])->name('logs.mark-trusted');
        Route::post('logs/bulk-destroy', [SecurityLogController::class, 'bulkDestroy'])->name('logs.bulk-destroy');
        Route::delete('/logs/{log}', [SecurityLogController::class, 'destroy'])->name('logs.destroy');
        Route::resource('trusted-ips', TrustedIpController::class);
        Route::resource('blocked-ips', BlockedIpsController::class)->only(['index', 'destroy']);
        Route::post('blocked-ips/bulk-destroy', [BlockedIpsController::class, 'bulkDestroy'])->name('blocked-ips.bulk-destroy');
    });

   
});


// === Frontend Routes ===
Route::prefix('{database}')->group(function () {
    Route::prefix('lesson')->group(function () {
        Route::get('/', [GradeOneController::class, 'index'])->name('class.index');
        Route::get('/{id}', [GradeOneController::class, 'show'])->name('frontend.class.show');
        Route::get('subjects/{subject}', [GradeOneController::class, 'showSubject'])->name('frontend.subjects.show');
        Route::get('subjects/{subject}/articles/{semester}/{category}', [GradeOneController::class, 'subjectArticles'])->name('frontend.subject.articles');
        Route::get('/articles/{article}', [GradeOneController::class, 'showArticle'])->name('frontend.articles.show');
        Route::get('files/download/{id}', [FileController::class, 'downloadFile'])->name('files.download');
    });

    // Keywords for the frontend
    Route::get('/keywords', [KeywordController::class, 'index'])->name('frontend.keywords.index');
    Route::get('/keywords/{keywords}', [KeywordController::class, 'indexByKeyword'])->name('keywords.indexByKeyword');

    //News for the frontend
    Route::get('/news', [FrontendNewsController::class, 'index'])->name('frontend.news.index');
    Route::get('/news/{id}', [FrontendNewsController::class, 'show'])->name('frontend.news.show');
    Route::get('/news/category/{category}', [FrontendNewsController::class, 'category'])->name('frontend.news.category');

    // Filter routes for news
    Route::get('news/filter', [FrontendNewsController::class, 'filterNewsByCategory'])->name('frontend.news.filter');

    // Categories for the frontend
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('frontend.categories.show');

});

// === Filter and API Routes ===
Route::get('/filter-files', [FilterController::class, 'index'])->name('files.filter');
Route::get('/api/subjects/{classId}', [FilterController::class, 'getSubjectsByClass']);
Route::get('/api/semesters/{subjectId}', [FilterController::class, 'getSemestersBySubject']);
Route::get('/api/files/{semesterId}', [FilterController::class, 'getFileTypesBySemester']);

// === Analytics Routes ===
 Route::get('/download/{file}', [FileController::class, 'showDownloadPage'])->name('download.page');
 Route::get('/download-wait/{file}', [FileController::class, 'processDownload'])->name('download.wait');
