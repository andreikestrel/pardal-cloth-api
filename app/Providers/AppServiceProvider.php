<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
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
        // UUID PKs across the app — enforce UUID as the default morph key type
        // so polymorphic relations (e.g. Spatie Media Library) use UUID columns.
        Schema::defaultMorphKeyType('uuid');
    }
}
