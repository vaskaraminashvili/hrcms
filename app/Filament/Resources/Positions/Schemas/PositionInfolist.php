<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PositionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('position_type')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->placeholder('-'),
                TextEntry::make('department.name')
                    ->label(__('filament.infolist.department')),
                TextEntry::make('date_start')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('date_end')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->placeholder('-'),
                TextEntry::make('act_number')
                    ->placeholder('-'),
                TextEntry::make('act_date')
                    ->date()
                    ->placeholder('-'),
                IconEntry::make('automative_renewal')
                    ->boolean()
                    ->placeholder('-'),
                TextEntry::make('salary')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('comment')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
