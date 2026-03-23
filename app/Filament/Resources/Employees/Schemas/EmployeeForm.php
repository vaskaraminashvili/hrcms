<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\Education;
use App\Enums\Gender;
use App\Enums\PersonalFile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('filament.tabs.container'))
                    ->tabs([
                        Tab::make(__('filament.tabs.basic_information'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament.name'))
                                    ->required(),
                                TextInput::make('surname')
                                    ->label(__('filament.surname'))
                                    ->required(),
                                TextInput::make('name_eng')
                                    ->label(__('filament.name_eng')),
                                TextInput::make('surrname_eng')
                                    ->label(__('filament.surrname_eng')),
                                TextInput::make('personal_number')
                                    ->label(__('filament.personal_number'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('filament.email'))
                                    ->email(),
                                DatePicker::make('birth_date')
                                    ->label(__('filament.birth_date'))
                                    ->required(),
                                Select::make('gender')
                                    ->options(Gender::class)
                                    ->default(__('filament.gender_default'))
                                    ->label(__('filament.gender')),

                                Section::make()
                                    ->schema([
                                        Radio::make('education')
                                            ->label(__('filament.education'))
                                            ->options([
                                                1 => __('filament.education_level.secondary'),
                                                2 => __('filament.education_level.higher'),
                                            ])
                                            ->inline()
                                            ->live()
                                            ->required(),
                                        Select::make('degree')
                                            ->label(__('filament.degree'))
                                            ->options(Education::class)
                                            ->visible(fn ($get): bool => (int) $get('education') === 2)
                                            ->required(fn ($get): bool => (int) $get('education') === 2),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                                TextInput::make('citizenship')
                                    ->label(__('filament.citizenship')),
                                TextInput::make('address')
                                    ->columnSpan(2)
                                    ->label(__('filament.address')),
                                TextInput::make('pysical_address')
                                    ->columnSpan(2)
                                    ->label(__('filament.pysical_address')),
                            ])
                            ->columns(2),
                        ...array_map(
                            function (PersonalFile $case) {
                                $schemaClass = $case->schemaClass();

                                return Tab::make(__('filament.personal_file.tabs.'.$case->value))
                                    ->badge(fn ($record) => $record?->{$case->relationship()}()->count() ?? 0)
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
                    ->activeTab(1)
                    ->vertical()
                    ->columnSpanFull(),
            ]);
    }
}
