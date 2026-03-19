<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\PersonalFile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('surname')
                                    ->required(),
                                TextInput::make('name_eng'),
                                TextInput::make('surrname_eng'),
                                TextInput::make('personal_number')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email(),
                                DatePicker::make('birth_date')
                                    ->required(),
                                TextInput::make('gender'),
                                TextInput::make('citizenship'),
                                TextInput::make('education')
                                    ->numeric(),
                                TextInput::make('degree'),
                                TextInput::make('address'),
                                TextInput::make('pysical_address'),
                            ])
                            ->columns(2),
                        ...array_map(
                            function (PersonalFile $case) {
                                $schemaClass = $case->schemaClass();

                                return Tab::make($case->label())
                                    ->schema([
                                        Repeater::make($case->relationship())
                                            ->relationship()
                                            ->schema($schemaClass::schema())
                                            ->collapsible()
                                            ->reorderable()
                                            ->columnSpanFull()
                                            ->itemLabel(function (array $state) use ($case): ?string {
                                                $field = $case->itemLabelField();
                                                $value = $state[$field]['ka'] ?? $state[$field]['en'] ?? null;

                                                return is_string($value) ? $value : null;
                                            }),
                                    ]);
                            },
                            PersonalFile::cases()
                        ),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
