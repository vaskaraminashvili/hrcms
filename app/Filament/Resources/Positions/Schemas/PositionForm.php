<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                    ->label('Employee')
                    ->disabled(fn (?Position $record): bool => $record !== null)
                    ->dehydrated()
                    ->required()
                    ->columnSpanFull(),
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Select::make('department_id')
                                    ->relationship('department', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('place_id')
                                    ->relationship('place', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('position_type')
                                    ->options(PositionType::class)
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),
                                DatePicker::make('date_start'),
                                DatePicker::make('date_end'),
                                TextInput::make('act_number'),
                                DatePicker::make('act_date'),
                                Select::make('status')
                                    ->options(PositionStatus::class)
                                    ->required(),

                                Radio::make('staff_type')
                                    ->inline()
                                    ->options([
                                        '0' => 'Contractual',
                                        '1' => 'Staff',
                                    ]),
                                Radio::make('clinical')
                                    ->inline()
                                    ->options([
                                        '0' => 'Clinical',
                                        '1' => 'Non-clinical',
                                    ])
                                    ->label('Clinical')
                                    ->visible(function ($get) {
                                        $positionType = $get('position_type');

                                        return $positionType->value === PositionType::AcademicPersonnel->value;
                                    }),
                                TextInput::make('clinical_text')
                                    ->label('Clinical Text')
                                    ->visible(fn ($get) => $get('position_type')?->value === PositionType::AcademicPersonnel->value)
                                    ->required(fn ($get) => $get('position_type')?->value === PositionType::AcademicPersonnel->value),
                                Toggle::make('automative_renewal')
                                    ->label('Automative Renewal')
                                    ->visible(fn ($get) => $get('position_type')?->value === PositionType::ContractedEmployee->value)
                                    ->required(fn ($get) => $get('position_type')?->value === PositionType::ContractedEmployee->value),
                                TextInput::make('salary')
                                    ->numeric(),
                                RichEditor::make('comment')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

            ]);
    }

    public static function configureForRelationManager(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Select::make('department_id')
                                    ->relationship('department', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('place_id')
                                    ->relationship('place', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('position_type')
                                    ->options(PositionType::class)
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('date_start'),
                                DatePicker::make('date_end'),
                                TextInput::make('act_number'),
                                DatePicker::make('act_date'),
                                Select::make('status')
                                    ->options(PositionStatus::class)
                                    ->required(),
                                Toggle::make('automative_renewal')
                                    ->label('Automative Renewal')
                                    ->visible(fn ($get): bool => $get('position_type') === PositionType::ContractedEmployee->value),
                                TextInput::make('salary')
                                    ->numeric(),
                                RichEditor::make('comment')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
