<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Enums\PositionStatus;
use App\Models\Position;
use App\Models\PositionType;
use Filament\Forms\Components\DatePicker;
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
    private const ACADEMIC_PERSONNEL_NAME = 'აკადემიური პერსონალი';

    public static function hasAcademicPersonnelPositionType(mixed $positionTypeIds): bool
    {
        $ids = collect($positionTypeIds)->flatten()->filter()->toArray();

        if (empty($ids)) {
            return false;
        }

        return PositionType::whereIn('id', $ids)
            ->where('name', self::ACADEMIC_PERSONNEL_NAME)
            ->exists();
    }

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
                                Select::make('position_types')
                                    ->relationship('positionTypes', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('date_start'),
                                DatePicker::make('date_end'),
                                TextInput::make('act_number'),
                                DatePicker::make('act_date'),
                                Select::make('status')
                                    ->options(PositionStatus::class)
                                    ->required(),
                                Toggle::make('staff_type')
                                    ->label('Staff Type'),
                                Toggle::make('clinical')
                                    ->label('Clinical')
                                    ->visible(fn ($get): bool => self::hasAcademicPersonnelPositionType($get('position_types'))),
                                TextInput::make('clinical_text')
                                    ->label('Clinical Text')
                                    ->visible(fn ($get): bool => self::hasAcademicPersonnelPositionType($get('position_types'))),
                                Toggle::make('automative_renewal')
                                    ->label('Automative Renewal')
                                    ->visible(function ($get) {
                                        $positionTypes = $get('position_type');
                                        $positionType = PositionType::query()
                                            ->where('id', $positionTypes)->first();

                                        return $positionType->name === self::ACADEMIC_PERSONNEL_NAME;
                                    }),
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
                                Select::make('position_types')
                                    ->relationship('positionTypes', 'name', fn (Builder $query) => $query->where('is_active', true))
                                    ->searchable()
                                    ->preload()
                                    ->multiple()
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
                                    ->label('Automative Renewal'),
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
