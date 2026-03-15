<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Enums\EnumsDepartmentColor;
use App\Enums\EnumsDepartmentType;
use App\Models\Department;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    /**
     * @return array<int, Component>
     */
    public static function getFormComponents(?Department $excludingRecord = null): array
    {
        $excludeIds = $excludingRecord
            ? $excludingRecord->descendants()->pluck('id')->push($excludingRecord->id)->toArray()
            : [];

        $parentOptions = Department::query()
            ->when($excludeIds, fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->pluck('name', 'id');

        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->nullable()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('type')
                ->options(collect(EnumsDepartmentType::cases())->mapWithKeys(
                    fn (EnumsDepartmentType $case) => [$case->value => $case->label()]
                ))
                ->nullable()
                ->searchable()
                ->hidden(fn (string $operation): bool => $operation === 'quickView'),

            Select::make('color')
                ->options(collect(EnumsDepartmentColor::cases())->mapWithKeys(
                    fn (EnumsDepartmentColor $case) => [$case->value => $case->label()]
                ))
                ->nullable()
                ->searchable()
                ->hidden(fn (string $operation): bool => $operation === 'quickView'),

            Select::make('parent_id')
                ->label('Parent Department')
                ->options($parentOptions)
                ->nullable()
                ->searchable()
                ->preload()
                ->hidden(fn (string $operation): bool => $operation === 'quickView'),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->columnSpanFull()
                ->hidden(fn (string $operation): bool => $operation === 'quickView'),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getFormComponents());
    }
}
