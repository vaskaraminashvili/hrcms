<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PositionForm
{
    public static function configure(Schema $schema, bool $withEmployee = true): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->relationship('employee', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                ->label(__('filament/admin/position_resource.employee_id'))
                ->disabled(fn (?Position $record): bool => $record !== null)
                ->dehydrated()
                ->required()
                ->columnSpanFull()
                ->visible($withEmployee),

            Tabs::make('Tabs')
                ->tabs([
                    Tab::make('Basic Information')
                        ->schema([
                            Select::make('department_id')
                    ->label(__('filament/admin/position_resource.department_id'))
                                ->relationship('department', 'name', fn (Builder $query) => $query->where('status', DepartmentStatus::ACTIVE))
                                ->required()
                                ->columnSpanFull(),

                            Select::make('place_id')
                    ->label(__('filament/admin/position_resource.place_id'))
                                ->relationship('place', 'name', fn (Builder $query) => $query->where('is_active', true))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(),

                            Select::make('position_type')
                    ->label(__('filament/admin/position_resource.position_type'))
                                ->options(PositionType::class)
                                ->required()
                                ->live()
                                ->columnSpanFull(),

                            DatePicker::make('date_start')
                    ->label(__('filament/admin/position_resource.date_start')),
                            DatePicker::make('date_end')
                    ->label(__('filament/admin/position_resource.date_end')),

                            TextInput::make('act_number')
                    ->label(__('filament/admin/position_resource.act_number')),
                            DatePicker::make('act_date')
                    ->label(__('filament/admin/position_resource.act_date')),

                            Select::make('status')
                    ->label(__('filament/admin/position_resource.status'))
                                ->options(PositionStatus::class)
                                ->required(),

                            Radio::make('staff_type')
                    ->label(__('filament/admin/position_resource.staff_type'))
                                ->inline()
                                ->options([
                                    '0' => 'Contractual',
                                    '1' => 'Staff',
                                ]),

                            Section::make()
                                ->schema([
                                    Radio::make('clinical')
                    ->label(__('filament/admin/position_resource.clinical'))
                                        ->inline()
                                        ->options([
                                            '0' => 'Clinical',
                                            '1' => 'Non-clinical',
                                        ])
                                        ->label('Clinical')
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),

                                    TextInput::make('clinical_text')
                                        ->label(__('filament/admin/position_resource.clinical_text'))
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type')))
                                        ->required(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),
                            Section::make()
                                ->schema([
                                    Toggle::make('automative_renewal')
                                        ->label(__('filament/admin/position_resource.automative_renewal'))
                                        ->visible(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type')))
                                        ->required(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type'))),
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type'))),

                            TextInput::make('salary')
                    ->label(__('filament/admin/position_resource.salary'))
                                ->numeric(),

                            RichEditor::make('comment')
                    ->label(__('filament/admin/position_resource.comment'))
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function positionTypeShowsClinical(mixed $positionType): bool
    {
        $type = $positionType instanceof PositionType
            ? $positionType
            : PositionType::tryFrom($positionType);

        return $type?->showsClinicalFields() ?? false;
    }

    private static function positionTypeShowsAutomativeRenewal(mixed $positionType): bool
    {
        $type = $positionType instanceof PositionType
            ? $positionType
            : PositionType::tryFrom($positionType);

        return $type?->showsAutomativeRenewal() ?? false;
    }
}
