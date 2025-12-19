<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Actions\Action;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\EditProfile;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employee')
            ->darkMode(false)
            ->font('Noto Sans Thai')
            ->path('/')
            ->login(Login::class)
            ->brandLogo(asset('storage/user.png'))
            ->brandLogoHeight('3.5rem')
            ->profile(EditProfile::class)
            ->registration(Register::class)
            ->globalSearch(false)
            ->topNavigation()
            ->userMenuItems([
                Action::make('switchmode')
                    ->icon('heroicon-o-arrows-right-left')
                    ->hidden(function () {
                        $role_name = auth()->user()->userBelongToRole?->name;
                        if (in_array($role_name, ['admin', 'super_admin'], true)) {
                            return 0;
                        } else {
                            return 1;
                        }
                    })
                    ->color('warning')
                    ->label('ไปโหมดแอดมิน')
                    ->url('/admin'),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->colors([
                'primary' => 'oklch(0.55 0.19 259.29)',
            ])
            ->discoverResources(in: app_path('Filament/Panel/Employee/Resources'), for: 'App\Filament\Panel\Employee\Resources')
            ->discoverPages(in: app_path('Filament/Panel/Employee/Pages'), for: 'App\Filament\Panel\Employee\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Panel/Employee/Widgets'), for: 'App\Filament\Panel\Employee\Widgets')
            ->widgets([])
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
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn() => Auth::check()
                    ? '<script>window.App = window.App || {}; window.App.userId = ' . Auth::id() . ';</script>'
                    : '' // ถ้าไม่ล็อกอินก็ไม่แทรกอะไรเลย
            );
    }
}
