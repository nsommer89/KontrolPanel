<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Filament\Widgets\KontrolPanelInfoWidget;
use App\Helpers\KTRLOptionsHelper;
use App\Models\KTRLOption;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Navigation\NavigationItem;

class KtrlPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->font('Ubuntu', provider: GoogleFontProvider::class)
            ->default()
            ->id('ktrl')
            ->brandLogo(asset('static/ktrl_logo_xl.png'))
            ->brandLogoHeight('4rem')
            // ->topNavigation()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->sidebarCollapsibleOnDesktop()
            ->path('/')
            ->login(CustomLogin::class)
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class)
            ->profile()
            ->breadcrumbs(false)
            ->colors([
                'primary' => '#1A2C43',   // navy blue from logo
                'gray'    => '#374151',   // text-appropriate gray
                'info'    => '#2563EB',   // blue-600
                'success' => '#10B981',   // emerald-500
                'warning' => '#F59E0B',   // amber-500
                'danger'  => '#EF4444',   // red-500
            ])
            ->tenant(Team::class)
            ->navigationItems([
                NavigationItem::make('phpMyAdmin')
                    ->url(function () {
                        $host = request()->getHost();
                        $pma_port = KTRLOptionsHelper::getInstance()->getPMAPort();
                        return "http://{$host}:{$pma_port}";
                    }, shouldOpenInNewTab: true)
                    ->icon('icon-pma')
                    ->group('Shortcuts')
                    ->sort(3),
            ])
            ->navigationGroups([
                NavigationGroup::make('Webhosting')
                    ->label(fn(): string => __('Webhosting'))
                    ->collapsible(false),
                NavigationGroup::make('System')
                    ->label(fn(): string => __('System'))
                    ->collapsible(false)
                    ->collapsed(),
                NavigationGroup::make('Shortcuts')
                    ->label(fn(): string => __('Shortcuts'))
                    ->collapsible(false)
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                KontrolPanelInfoWidget::class
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
            ]);
    }
}
