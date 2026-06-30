<?php

namespace App\Filament\Resources\Positions\RelationManagers;

use App\Filament\Resources\VacationTransfers\Schemas\VacationTransferForm;
use App\Filament\Resources\VacationTransfers\Tables\VacationTransfersTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VacationTransferRelationManager extends RelationManager
{
    protected static string $relationship = 'vacationTransfers';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.relation_managers.vacation_transfers.title');
    }

    public function form(Schema $schema): Schema
    {
        return VacationTransferForm::configure($schema, showPosition: false);
    }

    public function table(Table $table): Table
    {
        return VacationTransfersTable::configure($table, hidePositionColumn: true)
            ->recordTitleAttribute('from_year')
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.relation_managers.vacation_transfers.add_new_vacation_transfer'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['position_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
