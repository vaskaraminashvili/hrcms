<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\DepartmentType;
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
                    Select::make('type')
                        ->label(__('filament.type'))
                        ->options(DepartmentType::class)
                        ->default(DepartmentType::DEPARTMENT),
                    Select::make('parent_id')
                        ->label(__('filament.parent_id'))
                        ->options($parentOptions)
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->hint(fn ($state): string => $state
                            ? __('filament.department_parent_hint_level', [
                                'level' => (Department::find($state)?->ancestors()->count() ?? 0) + 2,
                            ])
                            : __('filament.department_parent_hint_root')
                        )
                        ->hidden(fn (string $operation): bool => $operation === 'quickView'),
                    TextInput::make('name')
                        ->label(__('filament.name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('vacancy_count')
                        ->label(__('filament.vacancy_count'))
                        ->numeric()
                        ->default(0),

                    Select::make('status')
                        ->label(__('filament.status'))
                        ->options(collect(DepartmentStatus::cases())->mapWithKeys(
                            fn (DepartmentStatus $case) => [$case->value => __("filament.department_status.{$case->value}")]
                        ))
                        ->default(__('filament.status_default')),
                ])
                ->columnSpanFull(),

            // Select::make('color')
            //     ->options(collect(EnumsDepartmentColor::cases())->mapWithKeys(
            //         fn (EnumsDepartmentColor $case) => [$case->value => $case->label(__('filament.color'))]
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
