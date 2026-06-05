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
Route::get('/artikel', [HomeController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [HomeController::class, 'artikelDetail'])->name('artikel.detail');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/news/{slug}', [HomeController::class, 'newsDetail'])->name('news.detail');
Route::get('/tentang', [HomeController::class, 'tentang'])->name('tentang');
Route::get('/mitra', [HomeController::class, 'mitra'])->name('mitra');
Route::get('/langganan', [HomeController::class, 'langganan'])->name('langganan');
Route::post('/langganan/kirim', [HomeController::class, 'kirimPembayaran'])->name('langganan.kirim')->middleware('auth');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

// Admin CMS Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');

    // Mitra Analis
    Route::get('/mitra', [\App\Http\Controllers\Admin\MitraController::class, 'index'])->name('mitra.index');

    // Users
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');

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

    // Posts & Categories
    Route::resource('posts', PostController::class);
    Route::post('categories', [PostController::class, 'storeCategory'])->name('categories.store');
    Route::delete('categories/{category}', [PostController::class, 'destroyCategory'])->name('categories.destroy');
});

require __DIR__.'/auth.php';
