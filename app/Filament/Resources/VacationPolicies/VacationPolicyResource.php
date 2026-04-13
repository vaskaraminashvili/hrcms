<?php

namespace App\Filament\Resources\VacationPolicies;

use App\Filament\Resources\VacationPolicies\Pages\CreateVacationPolicy;
use App\Filament\Resources\VacationPolicies\Pages\EditVacationPolicy;
use App\Filament\Resources\VacationPolicies\Pages\ListVacationPolicies;
use App\Filament\Resources\VacationPolicies\Schemas\VacationPolicyForm;
use App\Filament\Resources\VacationPolicies\Tables\VacationPoliciesTable;
use App\Models\VacationPolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VacationPolicyResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $model = VacationPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return VacationPolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacationPoliciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacationPolicies::route('/'),
            'create' => CreateVacationPolicy::route('/create'),
            'edit' => EditVacationPolicy::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.vacation_policies.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.vacation_policies.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.vacation_policies.plural_model_label');
    }
}
