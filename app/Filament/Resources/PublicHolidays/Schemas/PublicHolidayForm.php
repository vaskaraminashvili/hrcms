<?php

namespace App\Filament\Resources\PublicHolidays\Schemas;

use App\Enums\PublicHolidayKind;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PublicHolidayForm
{
    public static function configureForCreate(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('start_date')
                    ->required()
                    ->native(false)
                    ->label(__('filament.public_holiday_start_date')),
                DatePicker::make('end_date')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('start_date')
                    ->label(__('filament.public_holiday_end_date')),
                Select::make('kind')
                    ->options(collect(PublicHolidayKind::cases())->mapWithKeys(
                        fn (PublicHolidayKind $case) => [$case->value => $case->label()]
                    ))
                    ->default(PublicHolidayKind::Regular->value)
                    ->required()
                    ->label(__('filament.public_holiday_kind.title')),
                TextInput::make('name')
                    ->maxLength(255)
                    ->label(__('filament.public_holiday_name'))
                    ->placeholder(__('filament.public_holiday_name_placeholder')),
            ]);
    }

    public static function configureForEdit(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->label(__('filament.date')),
                Select::make('kind')
                    ->options(collect(PublicHolidayKind::cases())->mapWithKeys(
                        fn (PublicHolidayKind $case) => [$case->value => $case->label()]
                    ))
                    ->required()
                    ->label(__('filament.public_holiday_kind.title')),
                TextInput::make('name')
                    ->maxLength(255)
                    ->label(__('filament.public_holiday_name')),
            ]);
    }
}
