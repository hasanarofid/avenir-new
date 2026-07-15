<?php
$content = file_get_contents('routes/web.php');
$newRoute = "
    // Ownership Manual Inputs
    Route::get('desk-brief/ownership-manual', [\App\Http\Controllers\Admin\OwnershipManualInputController::class, 'index'])->name('admin.ownership.manual.index');
    Route::post('desk-brief/ownership-manual', [\App\Http\Controllers\Admin\OwnershipManualInputController::class, 'store'])->name('admin.ownership.manual.store');
    Route::delete('desk-brief/ownership-manual/{input}', [\App\Http\Controllers\Admin\OwnershipManualInputController::class, 'destroy'])->name('admin.ownership.manual.destroy');
";

// Insert it inside the admin prefix group, maybe right after Route::post('desk-brief/ownership'
$content = str_replace("Route::post('desk-brief/ownership', [\App\Http\Controllers\Admin\OwnershipController::class, 'upload'])->name('admin.ownership.upload');", "Route::post('desk-brief/ownership', [\App\Http\Controllers\Admin\OwnershipController::class, 'upload'])->name('admin.ownership.upload');\n" . $newRoute, $content);

file_put_contents('routes/web.php', $content);
