<?php

namespace App\Filament\Resources\Positions\Pages;

use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Services\PositionAttachmentHistoryService;
use App\Services\PositionFormPersistence;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

    protected bool $skipPositionObserverOnNextSave = false;

    public static function getNavigationLabel(): string
    {
        return __('filament.admin.edit_position.title');
    }

    public function getTitle(): string
    {
        return __('filament.admin.edit_position.title');
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema, true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge($data, Arr::except(
            $this->getRecord()->attributesToArray(),
            ['id', 'created_at', 'updated_at'],
        ));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $skipObserver = $this->skipPositionObserverOnNextSave;
        $this->skipPositionObserverOnNextSave = false;

        $oldValues = $record->replicate();

        if ($skipObserver) {
            Position::withoutEvents(
                fn () => PositionFormPersistence::updatePositionAndDetail($record, $data),
            );
        } else {
            PositionFormPersistence::updatePositionAndDetail($record, $data);
        }

        $record->refresh();
        // Get new values after update
        $newValues = $record->getAttributes();

        // Get only the changed attributes (dirty)
        $changedValues = $record->getDirty();

        // Build the properties in the format you want
        $properties = [
            'attributes' => $newValues,
            'old' => $oldValues->getAttributes(),
        ];

        activity()
            ->event('updated')
            ->performedOn($record)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log('Position updated'.($skipObserver ? ' (quick save)' : ''));

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('position_history')
                ->label(__('filament.position_history_title'))
                ->icon('heroicon-o-clock')
                ->url(function (): string {
                    /** @var Position $record */
                    $record = $this->getRecord();

                    $attributes['filters[department_id][value]'] = $record->department_id;
                    // $attributes['filters[place_id][value]'] = $record->place_id;
                    $attributes['filters[created_at][created_until]'] = now()->format('Y-m-d');

                    if ($record->employee?->name) {
                        $attributes['search'] = $record->employee->name.' '.$record->employee->surname;
                    }

                    return route('filament.admin.resources.position-histories.index', $attributes);
                })
                ->openUrlInNewTab(),
        ];

    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament.save'))
                ->color('gray')
                ->action(function (): void {
                    $this->skipPositionObserverOnNextSave = true;
                    try {
                        $this->save();
                    } finally {
                        $this->skipPositionObserverOnNextSave = false;
                    }
                })
                ->keyBindings(['mod+s']),
            Action::make('saveWithHistory')
                ->label(__('filament.save_history'))
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('filament.position_edit.modal_save_history_heading'))
                ->modalDescription(__('filament.position_edit.modal_save_history_description'))
                ->modalSubmitActionLabel(__('filament.position_edit.modal_save_history_submit'))
                ->action(function (PositionAttachmentHistoryService $attachmentHistoryService): void {
                    $this->form->validate();

                    /** @var Position $position */
                    $position = $this->getRecord();

                    $attachmentHistoryService->beginSaveWithHistory(
                        $position,
                        PositionAttachmentHistoryService::formStateWithMediaField($this->form),
                    );

                    $this->skipPositionObserverOnNextSave = false;

                    try {
                        $this->save();
                    } finally {
                        $this->skipPositionObserverOnNextSave = false;
                    }

                    $attachmentHistoryService->finalizeSaveWithHistory($position->refresh());

                    $this->form->loadStateFromRelationships(shouldHydrate: true);
                }),
            $this->getCancelFormAction(),
        ];
    }
}
