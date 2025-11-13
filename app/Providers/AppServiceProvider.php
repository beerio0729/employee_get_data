<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
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
        FilamentAsset::register([
             Css::make('noto_sans_thai', 'https://fonts.googleapis.com/css?family=Noto Sans Thai'),
        //     Js::make('pusher', 'https://js.pusher.com/8.4.0/pusher.min.js'),
        //     Js::make('process_emp_doc_event', asset('/storage/js/process_emp_doc_event.js')),
        ]);
    }
}
