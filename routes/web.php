<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\MigratedPasswordController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [HomeController::class, 'katalog'])->name('katalog');
Route::get('/katalog/{slug}', [HomeController::class, 'katalogDetail'])->name('katalog.detail');
Route::post('/katalog/{id}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::get('/artikel', [HomeController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [HomeController::class, 'artikelDetail'])->name('artikel.detail');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [HomeController::class, 'newsDetail'])->name('news.detail');
Route::get('/api/market-chart/{symbol}', [HomeController::class, 'marketChartApi'])->name('api.market-chart');

// Emiten Hub (V1)
Route::get('/emiten', [\App\Http\Controllers\EmitenHubController::class, 'index'])->name('emiten.index');
Route::get('/emiten/{symbol}', [\App\Http\Controllers\EmitenHubController::class, 'show'])->name('emiten.show');

// KI Brief (V1.5 moved to V1)
Route::get('/ki-brief', [\App\Http\Controllers\KIBriefController::class, 'index'])->name('ki-brief.index');

// Disclosure Radar (V1.5)
Route::get('/disclosure-radar', [\App\Http\Controllers\DisclosureController::class, 'index'])->name('disclosure.index');

Route::middleware('auth')->group(function () {
    Route::get('/watchlist', [\App\Http\Controllers\WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist/toggle/{tickerId}', [\App\Http\Controllers\WatchlistController::class, 'toggle'])->name('watchlist.toggle');
    Route::post('/katalog/{id}/bookmark', [\App\Http\Controllers\BookmarkController::class, 'toggle'])->name('katalog.bookmark');
});


Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/mitra', [HomeController::class, 'mitra'])->name('mitra');
Route::get('/langganan', [HomeController::class, 'langganan'])->name('langganan');
Route::post('/langganan/kirim', [HomeController::class, 'kirimPembayaran'])->name('langganan.kirim')->middleware('auth');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/migrate-setup', [MigratedPasswordController::class, 'showSetupForm'])->name('password.migrate.setup');
Route::post('/migrate-setup', [MigratedPasswordController::class, 'storeNewPassword'])->name('password.migrate.store');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authenticated Mitra Register Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mitra/register', [\App\Http\Controllers\MitraController::class, 'create'])->name('mitra.register');
    Route::post('/mitra/register', [\App\Http\Controllers\MitraController::class, 'store'])->name('mitra.register.store');
});

// Authenticated Mitra Routes
Route::middleware(['auth'])->prefix('mitra')->name('mitra.')->group(function () {
    // Route mitra lainnya yang butuh role 'mitra'
    Route::middleware(['role:mitra'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MitraController::class, 'dashboard'])->name('dashboard');
        Route::get('/researches', [\App\Http\Controllers\MitraController::class, 'researches'])->name('researches');
        Route::get('/profile', [\App\Http\Controllers\MitraController::class, 'profile'])->name('profile');
    });
});


// Admin CMS Routes
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Shared Routes for Admin & Team Research
    Route::middleware(['auth', 'role:admin|team_research'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Katalog Riset
        Route::delete('katalog-riset/bulk-delete', [\App\Http\Controllers\Admin\ResearchController::class, 'bulkDestroy'])->name('katalog-riset.bulk-destroy');
        Route::resource('katalog-riset', \App\Http\Controllers\Admin\ResearchController::class);
        
        // News (Berita Pasar)
        Route::delete('news/bulk-delete', [\App\Http\Controllers\Admin\NewsController::class, 'bulkDestroy'])->name('news.bulk-destroy');
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);

        // Artikel Edukasi
        Route::delete('articles/bulk-delete', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);

        // Posts & Categories
        Route::resource('posts', PostController::class);
        Route::post('categories', [PostController::class, 'storeCategory'])->name('categories.store');
        Route::delete('categories/{category}', [PostController::class, 'destroyCategory'])->name('categories.destroy');

        // Research Generator
        Route::get('/research-generator', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'index'])->name('research-generator.index');
        Route::get('/research-generator/create', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'create'])->name('research-generator.create');
        Route::post('/research-generator', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'store'])->name('research-generator.store');
        Route::get('/research-generator/{project}', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'show'])->name('research-generator.show');
        Route::post('/research-generator/{project}/generate', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'generate'])->name('research-generator.generate');
        Route::post('/research-generator/{project}/publish', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'publishToKatalog'])->name('research-generator.publish');
        Route::put('/research-generator/{project}/draft/{draft}', [\App\Http\Controllers\Admin\ResearchGeneratorController::class, 'updateDraft'])->name('research-generator.update-draft');

        // News Generator
        Route::get('/news-generator', [\App\Http\Controllers\Admin\NewsGeneratorController::class, 'index'])->name('news-generator.index');
        Route::post('/news-generator/generate', [\App\Http\Controllers\Admin\NewsGeneratorController::class, 'generate'])->name('news-generator.generate');
        Route::post('/news-generator/publish', [\App\Http\Controllers\Admin\NewsGeneratorController::class, 'publish'])->name('news-generator.publish');
    });

    // Admin Only Routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        // AI Logs (Audit)
        Route::get('/ai-logs', [\App\Http\Controllers\Admin\AILogController::class, 'index'])->name('ai-logs.index');
        
        // Payments
        Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{id}/verify', [\App\Http\Controllers\Admin\PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{id}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

        // Mitra Analis
        Route::get('/mitra', [\App\Http\Controllers\Admin\MitraController::class, 'index'])->name('mitra.index');
        Route::post('/mitra/{id}/approve', [\App\Http\Controllers\Admin\MitraController::class, 'approve'])->name('mitra.approve');
        Route::put('/mitra/{id}', [\App\Http\Controllers\Admin\MitraController::class, 'update'])->name('mitra.update');
        Route::delete('/mitra/{id}', [\App\Http\Controllers\Admin\MitraController::class, 'destroy'])->name('mitra.destroy');

        // Team Research
        Route::resource('team-research', \App\Http\Controllers\Admin\TeamResearchController::class)->except(['create', 'show', 'edit']);

        // Users
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::put('/users/{id}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update-role');
        Route::delete('/users/bulk-destroy', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('notifications.store');
        Route::put('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'update'])->name('notifications.update');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Pages
        Route::resource('pages', PageController::class);
        Route::put('pages/{page}/sections/{section}', [PageController::class, 'updateSection'])->name('pages.sections.update');

        // Emiten (Ticker)
        Route::post('/emitens/import', [\App\Http\Controllers\Admin\TickerController::class, 'import'])->name('emitens.import');
        Route::post('/emitens/generate-ai', [\App\Http\Controllers\Admin\TickerController::class, 'generateWithAI'])->name('emitens.generate-ai');
        Route::resource('emitens', \App\Http\Controllers\Admin\TickerController::class);
    });
});

require __DIR__.'/auth.php';
