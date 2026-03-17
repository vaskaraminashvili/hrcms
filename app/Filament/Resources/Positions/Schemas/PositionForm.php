<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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
                                    ->relationship('department', 'name')
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('date_start'),
                                DatePicker::make('date_end'),
                                TextInput::make('act_number'),
                                DatePicker::make('act_date'),
                                TextInput::make('status'),
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
