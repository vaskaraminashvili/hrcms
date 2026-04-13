<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\PositionStatus;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Services\PositionFormPersistence;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class PositionsRelationManager extends RelationManager
{
    protected static string $relationship = 'positions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.relation_managers.positions.title');
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema, false, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date_start')
            ->columns([
                TextColumn::make('department.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->sortable(),
                TextColumn::make('place.name')
                    ->limit(50)
                    ->tooltip(fn (string $state): string => $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PositionStatus $state): string => $state->getLabel())
                    ->color(fn (PositionStatus $state): string => $state->getColor())
                    ->alignCenter()
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where('status', 'like', '%'.$search.'%');
                    })
                    ->sortable(),
                TextColumn::make('act_number')
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('act_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('automative_renewal')
                    ->label(__('filament.relation_managers.positions.renewal'))
                    ->alignCenter()
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('salary')
                    ->label(__('filament.salary'))
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('comment')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('filament.relation_managers.positions.add_new_position'))
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $data = PositionFormPersistence::prepareDataForCreate($data);

                        return PositionFormPersistence::createFromValidatedData($data, $livewire->getOwnerRecord());
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalDescription(function (EditAction $action): HtmlString {
                        /** @var Position $record */
                        $record = $action->getRecord();
                        $record->loadMissing('employee');

                        $attributes['filters[department_id][value]'] = $record->department_id;
                        $attributes['filters[place_id][value]'] = $record->place_id;
                        $attributes['filters[created_at][created_until]'] = now()->format('Y-m-d');

                        if ($record->employee?->name) {
                            $attributes['search'] = $record->employee->name.' '.$record->employee->surname;
                        }

                        $url = route('filament.admin.resources.position-histories.index', $attributes);

                        return new HtmlString(
                            '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="fi-link fi-ac-link-action inline-flex items-center gap-x-1 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">'
                            .'<span>'.e(__('filament.position_history_title')).'</span>'
                            .'</a>'
                        );
                    })
                    ->modalFooterActions(function (EditAction $action): array {
                        return [
                            $action->makeModalSubmitAction('save', ['skipPositionObserver' => true])
                                ->label(__('filament.save'))
                                ->color('gray')
                                ->keyBindings(['mod+s']),
                            $action->makeModalSubmitAction('saveWithHistory', ['skipPositionObserver' => false])
                                ->label(__('filament.save_history'))
                                ->color('primary')
                                ->requiresConfirmation()
                                ->modalHeading(__('filament.position_edit.modal_save_history_heading'))
                                ->modalDescription(__('filament.position_edit.modal_save_history_description'))
                                ->modalSubmitActionLabel(__('filament.position_edit.modal_save_history_submit')),
                            Action::make('cancel')
                                ->label(__('filament.cancel'))
                                ->close()
                                ->color('gray'),
                        ];
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        $record = Position::query()->find($data['id']);

                        return array_merge($data, Arr::except(
                            $record?->attributesToArray() ?? [],
                            ['id', 'created_at', 'updated_at'],
                        ));
                    })
                    ->using(function (array $data, RelationManager $livewire, Model $record, ?Table $table): void {
                        $skipObserver = (bool) ($livewire->getMountedAction()?->getArguments()['skipPositionObserver'] ?? false);

                        if ($skipObserver) {
                            Position::withoutEvents(
                                fn () => PositionFormPersistence::updatePositionAndDetail($record, $data),
                            );
                        } else {
                            PositionFormPersistence::updatePositionAndDetail($record, $data);
                        }

                        $record->refresh();
                    }),
                Action::make('open_position_edit')
                    ->label(__('filament.relation_managers.positions.open_edit_in_new_tab'))
                    ->icon('heroicon-o-pencil')

                    ->url(fn (Position $record): string => route('filament.admin.resources.positions.edit', [
                        'record' => $record,
                    ]))
                    ->openUrlInNewTab(),
                Action::make('position_history')
                    ->label('')
                    ->icon('heroicon-o-clock')
                    ->url(function (Position $record): string {

                        $attributes['filters[department_id][value]'] = $record->department_id;
                        $attributes['filters[place_id][value]'] = $record->place_id;
                        $attributes['filters[created_at][created_until]'] = now()->format('Y-m-d');

                        if ($record->employee->name) {
                            $attributes['search'] = $record->employee->name.' '.$record->employee->surname;
                        }

                        return route('filament.admin.resources.position-histories.index', $attributes);
                    })
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
