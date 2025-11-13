<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use Filament\Support\Assets\Js;

use Filament\Support\Colors\Color;

use App\Filament\Pages\EditProfile;
use Filament\View\PanelsRenderHook;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\UserProfileWidget;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->plugin(SimpleLightBoxPlugin::make())
            ->id('admin')
            ->font('Noto Sans Thai')
            ->path('/')
            ->login()
            ->profile(EditProfile::class)
            ->databaseNotifications()
            //->databaseNotificationsPolling('3s')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                //Dashboard::class,
            ])
            ->topNavigation()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                UserProfileWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->assets(
                [
                    // **ใช้ Vite::asset() เพื่อให้ชี้ไปยัง public/build/assets/app-xxxxxx.js**
                    // 'resources/js/app.js' คือ Entry Point ที่กำหนดใน vite.config.js
                    Js::make('echo-scripts', Vite::asset('resources/js/echo.js')),
                ],
                // 'default' คือ Package Name
                'default'
            )->renderHook(
                // แทรก Script เข้าไปในส่วนท้ายของ Head หรือ Body เพื่อให้โค้ด Echo อ่านได้
                PanelsRenderHook::BODY_START,
                fn() => Auth::check()
                    ? '<script>window.App = window.App || {}; window.App.userId = ' . Auth::id() . ';</script>'
                    : '' // ถ้าไม่ล็อกอินก็ไม่แทรกอะไรเลย
            );
    }
}
