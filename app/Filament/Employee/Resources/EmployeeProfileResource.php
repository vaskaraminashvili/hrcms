<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeProfileResource\Pages\EditEmployeeProfile;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Filament\Support\original_request;

class EmployeeProfileResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $slug = 'profile';

    protected static ?string $navigationLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return EmployeeResource::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeResource::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeResource::table($table);
    }

    public static function getRelations(): array
    {
        return EmployeeResource::getRelations();
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditEmployeeProfile::route('/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('user_id', auth()->id());
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getIndexUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
    ): string {
        return static::getUrl('edit', $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function registerNavigationItems(): void
    {
        if (filled(static::getCluster())) {
            return;
        }

        if (static::getParentResourceRegistration()) {
            return;
        }

        if (! static::shouldRegisterNavigation()) {
            return;
        }

        if (! static::canAccess()) {
            return;
        }

        Filament::getCurrentOrDefaultPanel()
            ->navigationItems([
                NavigationItem::make(static::getNavigationLabel())
                    ->group(static::getNavigationGroup())
                    ->icon(static::getNavigationIcon())
                    ->activeIcon(static::getActiveNavigationIcon())
                    ->isActiveWhen(fn (): bool => original_request()->routeIs(static::getRouteBaseName().'.*'))
                    ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                    ->badgeTooltip(static::getNavigationBadgeTooltip())
                    ->sort(static::getNavigationSort())
                    ->url(static::getUrl('edit')),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->employee !== null;
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check()
            && (int) $record->user_id === (int) auth()->id();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.profile');
    }
}
