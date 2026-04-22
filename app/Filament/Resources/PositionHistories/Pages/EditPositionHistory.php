<?php

namespace App\Filament\Resources\PositionHistories\Pages;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Filament\Resources\PositionHistories\PositionHistoryResource;
use App\Filament\Resources\Positions\Schemas\PositionForm;
use App\Models\Position;
use App\Models\PositionHistory;
use App\Services\PositionFormPersistence;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditPositionHistory extends EditRecord
{
    protected static string $resource = PositionHistoryResource::class;

    public function getTitle(): string
    {
        return __('filament.position_history_edit_title');
    }

    public function form(Schema $schema): Schema
    {
        return PositionForm::configure(
            $this->defaultForm($schema)->model($this->getRecord()->position),
            withEmployee: true,
            employee: null,
            historySnapshot: $this->getRecord(),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var PositionHistory $history */
        $history = $this->getRecord();
        $position = $history->position;
        $snapshot = $history->snapshot ?? [];
        if (! is_array($snapshot)) {
            $snapshot = [];
        }

        $base = $position->exists
            ? Arr::except($position->attributesToArray(), ['created_at', 'updated_at'])
            : [];

        return array_merge($base, $snapshot);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var PositionHistory $history */
        $history = $record;
        $position = $history->position;

        Position::withoutEvents(
            fn () => PositionFormPersistence::updatePositionAndDetail($position, $data),
        );

        $position->refresh();

        $history->update([
            'changed_by' => auth()->id(),
            'snapshot' => Arr::except($position->toArray(), PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY),
            'changed_fields' => null,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                    $field->value => false,
                ])
                ->all(),
        ]);

        return $history->refresh();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
