<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\EnumsDepartmentColor;
use App\Models\Department;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
            Section::make()
                ->schema([
                    Select::make('parent_id')
                        ->label(__('filament/admin/department_resource.parent_id'))
                        ->options($parentOptions)
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->hint(fn ($state): string => $state
                            ? 'Level '.(Department::find($state)?->ancestors()->count() + 2)
                            : 'Level 1 (Root)'
                        )
                        ->hidden(fn (string $operation): bool => $operation === 'quickView'),
                    TextInput::make('name')
                        ->label(__('filament/admin/department_resource.name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('vacancy_count')
                        ->label(__('filament/admin/department_resource.vacancy_count'))
                        ->numeric()
                        ->default(0),

                    Select::make('status')
                        ->label(__('filament/admin/department_resource.status'))
                        ->options(collect(DepartmentStatus::cases())->mapWithKeys(
                            fn (DepartmentStatus $case) => [$case->value => $case->label(__('filament/admin/department_resource.status'))]
                        ))
                        ->default(DepartmentStatus::ACTIVE->value),
                ])
                ->columnSpanFull(),

            // Select::make('color')
            //     ->options(collect(EnumsDepartmentColor::cases())->mapWithKeys(
            //         fn (EnumsDepartmentColor $case) => [$case->value => $case->label(__('filament/admin/department_resource.color'))]
            //     ))
            //     ->nullable()
            //     ->searchable()
            //     ->hidden(fn (string $operation): bool => $operation === 'quickView'),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getFormComponents());
    }
}
