<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Actions\Action;
use App\Models\OrganizationLevel;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use App\Filament\Pages\EditProfile;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Auth\Register;
use Filament\Navigation\NavigationGroup;
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
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureFirsts\OrganizationStructureFirstResource;
use App\Filament\Panel\Admin\MultiResources\OrganizationStructureSeconds\OrganizationStructureSecondResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            // ->plugin(SimpleLightBoxPlugin::make())
            ->id('admin')
            ->darkMode(false)
            ->font('Noto Sans Thai')
            ->path('/admin')
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
                    ->color('warning')
                    ->label('ไปโหมดพนักงาน')
                    ->url('/'),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Setting')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->colors([
                'primary' => Color::Blue,
            ])
            //->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            //->discoverResources(in: app_path('Filament/Panel/Admin/Resources'), for: 'App\Filament\Panel\Admin\Resources')
            //->resources($this->organizationResources())
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
