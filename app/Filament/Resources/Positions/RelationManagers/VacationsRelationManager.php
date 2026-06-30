<?php

namespace App\Filament\Resources\Positions\RelationManagers;

use App\Filament\Resources\Vacations\Schemas\VacationForm;
use App\Filament\Resources\Vacations\Tables\VacationsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VacationsRelationManager extends RelationManager
{
    protected static string $relationship = 'vacations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.relation_managers.vacations.title');
    }

    public function form(Schema $schema): Schema
    {
        return VacationForm::configure($schema, false);
    }

    public function table(Table $table): Table
    {
        return VacationsTable::configure($table, hideEmployeeAndPositionColumns: true)
            ->recordTitleAttribute('start_date')
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.relation_managers.vacations.add_new_vacation'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['employee_id'] = $this->getOwnerRecord()->employee_id;
                        $data['position_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
