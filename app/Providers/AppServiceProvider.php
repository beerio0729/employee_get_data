<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use App\Events\ProcessEmpDocEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Filament\Support\Facades\FilamentAsset;

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
        // FilamentAsset::register([
        //     Js::make('echo', 'https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js'),
        //     Js::make('pusher', 'https://js.pusher.com/8.4.0/pusher.min.js'),
        //     Js::make('process_emp_doc_event', asset('/storage/js/process_emp_doc_event.js')),
    }
}
