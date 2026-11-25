<?php

namespace App\Providers\Filament;

use App\Filament\Review\Auth\ReviewLogin;
use App\Filament\Review\Auth\ReviewRenewPassword;
use App\Filament\Review\Pages\Dashboard;
use App\Filament\Review\Resources\Employees\EmployeeResource;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;

class ReviewPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('review')
            ->path('review')
            ->login(ReviewLogin::class)
            ->homeUrl(fn (): string => EmployeeResource::getUrl(panel: 'review'))
            ->brandName('TSMU HR')
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Review/Resources'), for: 'App\\Filament\\Review\\Resources')
            ->discoverPages(in: app_path('Filament/Review/Pages'), for: 'App\\Filament\\Review\\Pages')
            ->pages([
                Dashboard::class,
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
            ->plugins([
                FilamentLanguageSwitcherPlugin::make()
                    ->locales([
                        ['code' => 'en', 'name' => 'English', 'flag' => 'us'],
                        ['code' => 'ka', 'name' => 'Georgian', 'flag' => 'ge'],
                    ]),
                RenewPasswordPlugin::make()
                    ->forceRenewPassword()
                    ->renewPage(ReviewRenewPassword::class),
            ])
            ->font(
                fn () => app()->getLocale() === 'ka' ? 'Noto Sans Georgian' : 'Inter',
                provider: GoogleFontProvider::class,
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => Vite::withEntryPoints(['resources/css/filament/admin/custom.css'])->toHtml(),
            )
            ->globalSearch(false);
    }
}
