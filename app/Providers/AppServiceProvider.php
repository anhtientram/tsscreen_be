<?php

namespace App\Providers;

use App\Services\TvBoxApkStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TvBoxApkStorage::ensureDirectory();

        \Illuminate\Support\Facades\File::ensureDirectoryExists(
            rtrim(env('UPLOADS_ROOT') ?: public_path(), '/').'/uploads/livewire-tmp',
        );
    }
}
