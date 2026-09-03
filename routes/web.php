<?php

use App\Http\Controllers\Admin\TvBoxApkUploadController;
use App\Http\Controllers\LogViewerController;
use App\Services\TvBoxApkStorage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apk/tvbox.apk', function () {
    $path = TvBoxApkStorage::absolutePath();
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Content-Disposition' => 'attachment; filename="tvbox.apk"',
    ]);
})->name('releases.tvbox');

Route::get('/logs/app', [LogViewerController::class, 'index']);

Route::middleware(['web', 'auth:admin'])->group(function (): void {
    Route::post('/admin/apk/upload', [TvBoxApkUploadController::class, 'store'])
        ->name('admin.apk.upload');
});
