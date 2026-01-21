<?php

namespace App\Providers\Filament;

use App\Filament\Overrides\Filament\Panel\OverridePanel;
use Filament\Panel;
use Detection\MobileDetect;
use Filament\PanelProvider;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use App\Filament\Pages\EditProfile;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Cache;
use Filament\Http\Middleware\Authenticate;
use App\Models\Organization\OrganizationLevel;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{   public static bool $isiOS;
    public static bool $isAndroidOS;
    public function panel(Panel $panel): Panel
    {   
        $detect = new MobileDetect();
        static::$isiOS = $detect->isiOS();
        static::$isAndroidOS = $detect->isAndroidOS();
        return OverridePanel::make()
            ->default()
            ->id('admin')
            ->darkMode(false)
            ->maxContentWidth(fn() => Cache::get('top_navigation_' . auth()->id()) ? Width::ScreenExtraLarge : 'full')
            ->font('Noto Sans Thai')
            ->path('/admin')
            ->login(Login::class)
            ->brandLogo(asset('storage/user.png'))
            ->brandLogoHeight('3.5rem')
            ->breadcrumbs(false)
            ->profile(EditProfile::class)
            ->registration(Register::class)
            ->globalSearch(false)
            ->topNavigation(fn() => Cache::get('top_navigation_' . auth()->id()) ?? 0)
            ->userMenuItems([
                Action::make('switchmode')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->label('ไปโหมดพนักงาน')
                    ->url('/'),
                Action::make('menu-mode')
                    ->hidden(fn(): bool => static::$isAndroidOS || static::$isiOS)
                    ->label(fn() => Cache::get('top_navigation_' . auth()->id()) ? 'โหมดเมนูด้านข้าง' : 'โหมดเมนูด้านบน')
                    ->icon(fn() => Cache::get('top_navigation_' . auth()->id()) ? 'heroicon-o-chevron-double-right' : 'heroicon-o-chevron-double-up')
                    ->color('primary')
                    ->action(function () {
                        $key = 'top_navigation_' . auth()->id();
                        Cache::put($key, 1 - (int) Cache::get($key));
                        return redirect('/admin');
                    }),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverClusters(in: app_path('Filament/Panel/Admin/Clusters'), for: 'App\Filament\Panel\Admin\Clusters')
            //->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverResources(in: app_path('Filament/Panel/Admin/Resources'), for: 'App\Filament\Panel\Admin\Resources')
            ->resources($this->organizationResources())
            //->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverPages(in: app_path('Filament/Panel/Admin/Pages'), for: 'App\Filament\Panel\Admin\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Panel/Admin/Widgets'), for: 'App\Filament\Panel\Admin\Widgets')
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

    protected function organizationResources(): array
    {
        $OrgLevel = OrganizationLevel::get();
        $resources = [];

        if ($OrgLevel->isNotEmpty()) {
            foreach ($OrgLevel->pluck('level') as $sort) {
                // map level_sort เป็นชื่อ class
                $map = [
                    1 => 'First',
                    2 => 'Second',
                    3 => 'Third',
                    4 => 'Fourth',
                    5 => 'Fifth',
                    6 => 'Sixth',
                    7 => 'Seventh',
                ];

                $level = $map[$sort] ?? null;
                if ($level) {
                    $class = "App\\Filament\\Panel\\Admin\\MultiResources\\OrganizationStructure{$level}s\\OrganizationStructure{$level}Resource";
                    $resources[] = $class;
                }
            }
        }

        return $resources;
    }
}
    