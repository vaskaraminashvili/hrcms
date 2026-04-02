<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('employee_image')
                    ->circular()
                    ->label('')
                    ->collection('employee_image'),
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->name.' '.$record->surname;
                    })
                    ->searchable(['name', 'surname']),

                TextColumn::make('name_eng')
                    ->label(__('filament.name_eng'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->name_eng.' '.$record->surrname_eng;
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(['name_eng', 'surrname_eng']),

                TextColumn::make('personal_number')
                    ->badge()
                    ->label(__('filament.personal_number'))
                    ->formatStateUsing(function (string $state, Employee $record): string {
                        return $record->personal_number;
                    })
                    ->color('success')
                    ->searchable(),
                TextColumn::make('positions_count')
                    ->label(__('filament.positions_count'))
                    ->alignCenter()
                    ->icon('heroicon-o-briefcase')
                    ->counts('positions')
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament.email'))
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label(__('filament.birth_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->color(fn (Employee $record): string => $record->status->getColor())
                    ->icon(fn (Employee $record): string => $record->status->getIcon())
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(__('filament.gender'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('citizenship')
                    ->label(__('filament.citizenship'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('education')
                    ->label(__('filament.education'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('degree')
                    ->label(__('filament.degree'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address')
                    ->label(__('filament.address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pysical_address')
                    ->label(__('filament.pysical_address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filament.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('filament.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.status'))
                    ->options(EmployeeStatusEnum::class)
                    ->default(EmployeeStatusEnum::ACTIVE->value),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->filtersFormColumns(2);
    }
}
