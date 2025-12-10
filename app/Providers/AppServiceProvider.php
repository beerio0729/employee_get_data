<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use App\Events\ProcessEmpDocEvent;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;

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
            //Js::make('font-awesome', 'https://kit.fontawesome.com/22a2f0fe70.js'),
            Css::make('filament-overrides', Vite::asset('resources/css/filament-overrides.css')),
            Js::make('echo-scripts', Vite::asset('resources/js/echo.js')),
        ]);

        FilamentColor::register([
            'indigo' => Color::Indigo,
        ]);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('line', \SocialiteProviders\Line\Provider::class);
        });
    }
}
