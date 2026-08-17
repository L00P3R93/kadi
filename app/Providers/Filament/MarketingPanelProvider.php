<?php

namespace App\Providers\Filament;

use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Enums\ThemeMode;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MarketingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('marketing')
            ->path('marketing')
            ->viteTheme('resources/css/filament/marketing/theme.css')
            ->homeUrl('/ad-wallet')
            ->brandName('Marketing')
            ->authGuard('web')
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->defaultThemeMode(ThemeMode::Dark)
            ->darkMode()
            ->colors([
                'primary' => Color::Yellow,
                'secondary' => Color::Gray,
                'info' => Color::Cyan,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'danger' => Color::Red,
                'purple' => Color::Purple,
                'orange' => Color::Orange,
                'blue' => Color::Blue,
                'pink' => Color::Pink,
                'amber' => Color::Amber,
                'teal' => Color::Teal,
                'red' => Color::Red,
                'green' => Color::Green,
                'indigo' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Marketing/Resources'), for: 'App\Filament\Marketing\Resources')
            ->discoverPages(in: app_path('Filament/Marketing/Pages'), for: 'App\Filament\Marketing\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Marketing/Widgets'), for: 'App\Filament\Marketing\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->databaseNotifications(position: DatabaseNotificationsPosition::Sidebar)
            ->databaseNotificationsPolling('30s')
            ->spa();
    }
}
