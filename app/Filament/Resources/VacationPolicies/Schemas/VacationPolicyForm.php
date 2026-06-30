<?php

namespace App\Filament\Resources\VacationPolicies\Schemas;

use App\Enums\PositionType;
use App\Enums\StatusEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VacationPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('position_type')
                    ->label(__('filament.position_type'))
                    ->options(PositionType::class)
                    ->required(),
                TextInput::make('name')
                    ->label(__('filament.name'))
                    ->required(),
                TextInput::make('description')
                    ->label(__('filament.description'))
                    ->required(),
                Select::make('status')
                    ->label(__('filament.status'))
                    ->options(StatusEnum::class)
                    ->required(),
                Section::make('settings')
                    ->schema([
                        Repeater::make('settings')
                            ->schema([
                                TextInput::make('key')->required(),
                                TextInput::make('value')->required(),
                            ])
                            ->columns(2)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
