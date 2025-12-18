<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Filament\Overrides\Filament\Schemas\Components\Tab as MyTab;
use App\Filament\Overrides\Filament\Support\Enums\IconSize as MyIconSize;
use App\Filament\Overrides\Filament\Widgets\StatsOverviewWidget\Stat as MyStat;
use Filament\Support\Enums\IconSize as BaseIconSize;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {   
        $this->app->bind(Tab::class, MyTab::class);
        $this->app->bind(Stat::class, MyStat::class);
        class_alias(MyIconSize::class, BaseIconSize::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('noto_sans_thai', 'https://fonts.googleapis.com/css?family=Noto Sans Thai'),
            //Css::make('progress', 'https://www.w3schools.com/w3css/5/w3.css'),
            //Js::make('font-awesome', 'https://kit.fontawesome.com/22a2f0fe70.js'),
            Css::make('filament-overrides', Vite::asset('resources/css/filament-overrides.css')),
            Js::make('echo-scripts', Vite::asset('resources/js/echo.js')),
        ]);

        FilamentColor::register([
            'mycolor' => [
                50 => 'oklch(0.95 0.10 259.29)',
                100 => 'oklch(0.90 0.15 259.29)',
                200 => 'oklch(0.80 0.17 259.29)',
                300 => 'oklch(0.70 0.18 259.29)',
                400 => 'oklch(0.60 0.19 259.29)',
                500 => 'oklch(0.55 0.19 259.29)',
                600 => 'oklch(0.50 0.20 259.29)',
                700 => 'oklch(0.40 0.21 259.29)',
                800 => 'oklch(0 .30 0.22 259.29)',
                900 => 'oklch(0.20 0.25 259.29)',   
            ],
        ]);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('line', \SocialiteProviders\Line\Provider::class);
        });
    }
}
