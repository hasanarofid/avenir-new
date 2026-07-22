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
use App\Http\Controllers\SitemapController;

// SEO & Legal Routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/syarat-penggunaan', [HomeController::class, 'syaratPenggunaan'])->name('syarat-penggunaan');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/katalog', [HomeController::class, 'katalog'])->name('katalog');
Route::get('/katalog/{slug}', [HomeController::class, 'katalogDetail'])->name('katalog.detail');
Route::get('/katalog/{id}/download', [HomeController::class, 'downloadKatalogPdf'])->name('katalog.download');
Route::post('/katalog/{id}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::get('/artikel', [HomeController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [HomeController::class, 'artikelDetail'])->name('artikel.detail');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [HomeController::class, 'newsDetail'])->name('news.detail');
Route::get('/api/market-chart/{symbol}', [HomeController::class, 'marketChartApi'])->name('api.market-chart');

// Interactions
Route::post('/interaction/{type}/{id}/like', [\App\Http\Controllers\InteractionController::class, 'toggleLike'])->name('interaction.like');
Route::post('/interaction/{type}/{id}/comment', [\App\Http\Controllers\InteractionController::class, 'addComment'])->name('interaction.comment');
Route::post('/interaction/{type}/{id}/share', [\App\Http\Controllers\InteractionController::class, 'incrementShare'])->name('interaction.share');
// Market Intelligence (Desk Brief)
Route::get('/desk-brief', [\App\Http\Controllers\DeskBriefController::class, 'index'])->name('desk-brief.index');
Route::get('/desk-brief-mockup', [\App\Http\Controllers\DeskBriefController::class, 'mockup'])->name('desk-brief.mockup');
Route::get('/desk-brief/what-changed', [\App\Http\Controllers\DeskBriefController::class, 'whatChanged'])->name('desk-brief.what-changed');
Route::get('/desk-brief/ownership-intelligence', [\App\Http\Controllers\DeskBriefController::class, 'ownership'])->name('desk-brief.ownership');
Route::get('/desk-brief/ownership-intelligence-mockup', [\App\Http\Controllers\DeskBriefController::class, 'ownershipMockup'])->name('desk-brief.ownership-mockup');

// Public API: Ownership Intelligence data (membaca JSON file langsung, tanpa auth admin)
Route::get('/desk-brief/ownership-intelligence/data', [\App\Http\Controllers\Admin\OwnershipController::class, 'getOwnershipData'])->name('desk-brief.ownership.public-data');


// Market Tickers API for News Marquee
Route::get('/api/market-tickers', [\App\Http\Controllers\EmitenHubController::class, 'tickers'])->name('emiten.tickers');

// Master Stock API — emiten list dengan sektor, sub-industry, logo (publik, no auth)
Route::get('/api/master-stocks', [\App\Http\Controllers\Admin\MasterStockController::class, 'apiList'])->name('master-stock.api-list');


// Deprecated Market Hub Routes
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


Route::get('/p/{slug}', [\App\Http\Controllers\PageController::class, 'show'])->name('page.show');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/mitra', [HomeController::class, 'mitra'])->name('mitra');
Route::get('/langganan', [HomeController::class, 'langganan'])->name('langganan');
Route::post('/langganan/trial', [HomeController::class, 'aktifkanTrial'])->name('langganan.trial')->middleware('auth');
Route::post('/langganan/kirim', [HomeController::class, 'kirimPembayaran'])->name('langganan.kirim')->middleware('auth');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('google.login');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('google.callback');

Route::get('/migrate-setup', [MigratedPasswordController::class, 'showSetupForm'])->name('password.migrate.setup');
Route::post('/migrate-setup', [MigratedPasswordController::class, 'storeNewPassword'])->name('password.migrate.store');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin') || $user->hasRole('tim_internal')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('mitra')) {
        return redirect()->route('mitra.dashboard');
    }
    // Fallback for standard users without specific dashboard
    return Inertia::render('User/Dashboard', ['user' => $user]);
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
        Route::delete('articles/bulk-delete', [\App\Http\Controllers\Mitra\ArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');
        Route::patch('articles/{article}/toggle-editor-pick', [\App\Http\Controllers\Mitra\ArticleController::class, 'toggleEditorPick'])->name('articles.toggle-editor-pick');
        Route::resource('articles', \App\Http\Controllers\Mitra\ArticleController::class);

        Route::delete('researches/bulk-delete', [\App\Http\Controllers\Mitra\ResearchController::class, 'bulkDestroy'])->name('researches.bulk-destroy');
        Route::resource('researches', \App\Http\Controllers\Mitra\ResearchController::class);
        Route::get('/profile', [\App\Http\Controllers\MitraController::class, 'profile'])->name('profile');
    });
});


// Admin CMS Routes
Route::prefix('admin')->name('admin.')->group(function () {

    // Shared Routes for Admin & Tim Internal
    Route::middleware(['auth', 'role:admin|tim_internal'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Katalog Riset
        Route::delete('katalog-riset/bulk-delete', [\App\Http\Controllers\Admin\ResearchController::class, 'bulkDestroy'])->name('katalog-riset.bulk-destroy');
        Route::resource('katalog-riset', \App\Http\Controllers\Admin\ResearchController::class);

        // News (Berita Pasar)
        Route::delete('news/bulk-delete', [\App\Http\Controllers\Admin\NewsController::class, 'bulkDestroy'])->name('news.bulk-destroy');
        Route::patch('news/{news}/toggle-featured', [\App\Http\Controllers\Admin\NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);

        // Artikel Edukasi
        Route::delete('articles/bulk-delete', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkDestroy'])->name('articles.bulk-destroy');
        Route::patch('articles/{article}/toggle-editor-pick', [\App\Http\Controllers\Admin\ArticleController::class, 'toggleEditorPick'])->name('articles.toggle-editor-pick');
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

        // Desk Brief
        Route::get('/desk-brief', [\App\Http\Controllers\Admin\DeskBriefController::class, 'index'])->name('desk-brief.index');
        Route::post('/desk-brief/upload-pdf', [\App\Http\Controllers\Admin\DeskBriefController::class, 'uploadPdf'])->name('desk-brief.upload-pdf');
        Route::post('/desk-brief/upload-ihsg-csv', [\App\Http\Controllers\Admin\DeskBriefController::class, 'uploadIhsgCsv'])->name('desk-brief.upload-ihsg-csv');
        Route::post('/desk-brief/upload-masterlist', [\App\Http\Controllers\Admin\DeskBriefController::class, 'uploadMasterlist'])->name('desk-brief.upload-masterlist');
        Route::post('/desk-brief/upload-foreign-flow', [\App\Http\Controllers\Admin\DeskBriefController::class, 'uploadForeignFlow'])->name('desk-brief.upload-foreign-flow');
        Route::post('/desk-brief/upload-ringkasan-saham', [\App\Http\Controllers\Admin\DeskBriefController::class, 'uploadRingkasanSaham'])->name('desk-brief.upload-ringkasan-saham');

        Route::get('/desk-brief/{id}/edit', [\App\Http\Controllers\Admin\DeskBriefController::class, 'edit'])->name('desk-brief.edit');
        Route::put('/desk-brief/{id}', [\App\Http\Controllers\Admin\DeskBriefController::class, 'update'])->name('desk-brief.update');
        Route::delete('/desk-brief/{id}', [\App\Http\Controllers\Admin\DeskBriefController::class, 'destroy'])->name('desk-brief.destroy');
        Route::post('/desk-brief/{id}/publish', [\App\Http\Controllers\Admin\DeskBriefController::class, 'publish'])->name('desk-brief.publish');
        Route::post('/desk-brief/tester/run', [\App\Http\Controllers\Admin\DeskBriefController::class, 'runTester'])->name('desk-brief.tester.run');

        // Desk Brief - Ownership Intelligence
        Route::get('/desk-brief/ownership', [\App\Http\Controllers\Admin\OwnershipController::class, 'index'])->name('desk-brief.ownership.index');
        Route::get('/desk-brief/ownership/data', [\App\Http\Controllers\Admin\OwnershipController::class, 'getOwnershipData'])->name('desk-brief.ownership.data');
        Route::post('/desk-brief/ownership/upload', [\App\Http\Controllers\Admin\OwnershipController::class, 'upload'])->name('desk-brief.ownership.upload');
        Route::delete('/desk-brief/ownership/{id}', [\App\Http\Controllers\Admin\OwnershipController::class, 'destroy'])->name('desk-brief.ownership.destroy');

        // Master Stock (Emiten)
        Route::get('/master-stock', [\App\Http\Controllers\Admin\MasterStockController::class, 'index'])->name('master-stock.index');
        Route::post('/master-stock/import', [\App\Http\Controllers\Admin\MasterStockController::class, 'import'])->name('master-stock.import');
        Route::post('/master-stock/sync-logos', [\App\Http\Controllers\Admin\MasterStockController::class, 'syncLogos'])->name('master-stock.sync-logos');
        Route::put('/master-stock/{code}', [\App\Http\Controllers\Admin\MasterStockController::class, 'update'])->name('master-stock.update');
        Route::delete('/master-stock/{code}', [\App\Http\Controllers\Admin\MasterStockController::class, 'destroy'])->name('master-stock.destroy');

        // EOD Uploads
        Route::get('/eod-uploads', [\App\Http\Controllers\Admin\EodUploadController::class, 'index'])->name('eod-uploads.index');
        Route::post('/eod-uploads', [\App\Http\Controllers\Admin\EodUploadController::class, 'store'])->name('eod-uploads.store');

        // Internal APIs
        Route::get('/api/stocks/{code}/historical', [\App\Http\Controllers\Api\StockHistoricalController::class, 'show'])->name('api.stocks.historical');


    });


    // Admin Only Routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        // AI Logs (Audit)
        Route::get('/ai-logs', [\App\Http\Controllers\Admin\AILogController::class, 'index'])->name('ai-logs.index');

        // Activity Logs
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');


        // Payments
        Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{id}/verify', [\App\Http\Controllers\Admin\PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{id}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');

        // Subscription Packages
        Route::get('/packages', [\App\Http\Controllers\Admin\SubscriptionPackageController::class, 'index'])->name('packages.index');
        Route::put('/packages/{id}', [\App\Http\Controllers\Admin\SubscriptionPackageController::class, 'update'])->name('packages.update');

        // Mitra Analis
        Route::get('/mitra', [\App\Http\Controllers\Admin\MitraController::class, 'index'])->name('mitra.index');
        Route::post('/mitra/{id}/approve', [\App\Http\Controllers\Admin\MitraController::class, 'approve'])->name('mitra.approve');
        Route::put('/mitra/{id}', [\App\Http\Controllers\Admin\MitraController::class, 'update'])->name('mitra.update');
        Route::delete('/mitra/{id}', [\App\Http\Controllers\Admin\MitraController::class, 'destroy'])->name('mitra.destroy');

        // Pool Mitra
        Route::get('/pool', [\App\Http\Controllers\Admin\PoolController::class, 'index'])->name('pool.index');
        Route::post('/pool', [\App\Http\Controllers\Admin\PoolController::class, 'store'])->name('pool.store');

        // Tim Internal
        Route::resource('tim-internal', \App\Http\Controllers\Admin\TimInternalController::class)->except(['create', 'show', 'edit']);

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

require __DIR__ . '/auth.php';

Route::get('/mitra-pool-simulator', function () {
    $service = new \App\Services\Mitra\MitraPayoutService();
    $engine = new \App\Services\Mitra\MitraScoringEngine();

    $period = \App\Models\PartnerPayoutPeriod::firstOrCreate(
        ['period_month' => '2026-07-01'],
        ['net_subscription_revenue' => 25000000, 'pool_rate' => 0.20]
    );

    $partner1 = \App\Models\PartnerProfile::firstOrCreate(
        ['display_name' => 'Fikri Analyst (Demo)'],
        ['analyst_level' => 'Avenir Select Analyst']
    );

    $partner2 = \App\Models\PartnerProfile::firstOrCreate(
        ['display_name' => 'Hasan Analyst (Demo)'],
        ['analyst_level' => 'Contributor']
    );

    // Score calculation
    $score1 = $engine->calculateFinalScore(150, 80, 90, 50, 90, 'Equity Research', $partner1->analyst_level);
    $score2 = $engine->calculateFinalScore(80, 70, 50, 20, 80, 'Market Note', $partner2->analyst_level);

    $partnerScores = [
        $partner1->id => $score1,
        $partner2->id => $score2,
    ];

    $processedPeriod = $service->processPayoutPeriod($period, $partnerScores);
    $payouts = $processedPeriod->payouts()->with('partner')->get();

    $html = "<div style='font-family: Arial; padding: 40px;'>";
    $html .= "<h2>Avenir Mitra Pool Simulator (Demo)</h2>";
    $html .= "<p>Net Subscription Revenue: <b>Rp " . number_format($processedPeriod->net_subscription_revenue, 0, ',', '.') . "</b></p>";
    $html .= "<p>Mitra Pool Amount (20%): <b>Rp " . number_format($processedPeriod->mitra_pool_amount, 0, ',', '.') . "</b></p>";
    $html .= "<hr><table border='1' cellpadding='10' cellspacing='0' style='width: 100%; text-align: left;'>";
    $html .= "<tr style='background: #f4f4f4;'><th>Mitra Name</th><th>Analyst Level</th><th>Partner Score</th><th>Payout Amount</th></tr>";

    foreach ($payouts as $payout) {
        $html .= "<tr>";
        $html .= "<td>" . $payout->partner->display_name . "</td>";
        $html .= "<td>" . $payout->partner->analyst_level . "</td>";
        $html .= "<td>" . number_format($payout->partner_score, 2) . "</td>";
        $html .= "<td><b>Rp " . number_format($payout->payout_amount, 0, ',', '.') . "</b></td>";
        $html .= "</tr>";
    }

    $html .= "</table></div>";

    return $html;
});
