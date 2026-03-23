<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Employee;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('surname'),
                TextEntry::make('name_eng')
                    ->placeholder('-'),
                TextEntry::make('surrname_eng')
                    ->placeholder('-'),
                TextEntry::make('personal_number'),
                TextEntry::make('email')
                    ->label(__('filament.email'))
                    ->placeholder('-'),
                TextEntry::make('birth_date')
                    ->date(),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('citizenship')
                    ->placeholder('-'),
                TextEntry::make('education')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('degree')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('pysical_address')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Employee $record): bool => $record->trashed()),
            ]);
    }
}
