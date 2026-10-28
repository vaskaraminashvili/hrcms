<?php

namespace App\Services;

use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Models\Position;
use App\Models\PositionHistory;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PositionAttachmentHistoryService
{
    public const COLLECTION = 'position';

    public const FORM_FIELD = 'position_file_attachments_attachments';

    private ?SaveContext $context = null;

    /**
     * @param  array<string, mixed>  $formState
     */
    public function beginSaveWithHistory(Position $position, array $formState): void
    {
        $position->loadMissing('media');

        $this->context = new SaveContext(
            historyCountBefore: $position->histories()->count(),
            mediaBeforeSave: $this->snapshotPositionMedia($position),
            keptMediaUuids: $this->keptMediaUuidsFromFormState($formState),
        );

        $removedMedia = $this->removedMediaFromFormState($position, $this->context->keptMediaUuids);

        if ($removedMedia->isEmpty()) {
            return;
        }

        $provisionalHistory = $this->createHistoryRecord($position);
        $this->context->provisionalHistoryId = $provisionalHistory->id;
        $this->context->movedRemovedMedia = $this->moveMediaToHistory($removedMedia, $provisionalHistory);
    }

    /**
     * @return array<string, mixed>
     */
    public static function formStateWithMediaField(Schema $form): array
    {
        return [
            self::FORM_FIELD => self::resolveMediaFieldState($form),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function formStateWithMediaFieldFromMountedAction(object $livewire): array
    {
        return [
            self::FORM_FIELD => self::resolveMediaFieldStateFromMountedAction($livewire),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function resolveMediaFieldState(Schema $form): array
    {
        $component = $form->getComponent(
            fn (mixed $component): bool => $component instanceof SpatieMediaLibraryFileUpload
                && $component->getName() === self::FORM_FIELD,
            withActions: false,
            withHidden: true,
        );

        if (! $component instanceof SpatieMediaLibraryFileUpload) {
            return [];
        }

        $state = $component->getRawState();

        if (! is_array($state) || $state === []) {
            $state = $component->getState();
        }

        return is_array($state) ? $state : [];
    }

    /**
     * @return array<string, string>
     */
    public static function resolveMediaFieldStateFromMountedAction(object $livewire): array
    {
        if (! method_exists($livewire, 'getMountedActionSchemaName') || ! method_exists($livewire, 'getSchema')) {
            return [];
        }

        $schemaName = $livewire->getMountedActionSchemaName();

        if (blank($schemaName)) {
            return [];
        }

        $schema = $livewire->getSchema($schemaName);

        if (! $schema instanceof Schema) {
            return [];
        }

        return self::resolveMediaFieldState($schema);
    }

    public function finalizeSaveWithHistory(Position $position): void
    {
        if (! $this->context instanceof SaveContext) {
            return;
        }

        $context = $this->context;
        $this->context = null;

        $position->refresh();
        $position->loadMissing('media');

        $finalHistory = $this->resolveFinalHistoryRecord($position, $context);
        $this->consolidateProvisionalHistory($context, $finalHistory);

        $movedSuperseded = $this->transferSupersededAfterSave(
            $position,
            $context->mediaBeforeSave,
            $finalHistory,
        );

        if ($context->movedRemovedMedia->isNotEmpty() || $movedSuperseded->isNotEmpty()) {
            $this->recordAttachmentDiff(
                $finalHistory,
                $context->mediaBeforeSave,
                $position->getMedia(self::COLLECTION),
                $context->movedRemovedMedia,
                $movedSuperseded,
            );
        }
    }

    /**
     * @return Collection<int, Media>
     */
    public function snapshotPositionMedia(Position $position): Collection
    {
        return $position->getMedia(self::COLLECTION);
    }

    /**
     * @param  array<string, mixed>  $formState
     * @return list<string>
     */
    public function keptMediaUuidsFromFormState(array $formState): array
    {
        $raw = $formState[self::FORM_FIELD] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_keys($raw), fn (mixed $uuid): bool => is_string($uuid) && $uuid !== ''));
    }

    /**
     * @param  list<string>  $keptUuids
     * @return Collection<int, Media>
     */
    private function removedMediaFromFormState(Position $position, array $keptUuids): Collection
    {
        return $this->snapshotPositionMedia($position)
            ->filter(fn (Media $media): bool => ! in_array($media->uuid, $keptUuids, true));
    }

    /**
     * @param  Collection<int, Media>  $media
     * @return Collection<int, Media>
     */
    private function moveMediaToHistory(Collection $media, PositionHistory $history): Collection
    {
        return $media->map(function (Media $item) use ($history): Media {
            return $item->move($history, self::COLLECTION);
        });
    }

    private function resolveFinalHistoryRecord(Position $position, SaveContext $context): PositionHistory
    {
        if ($position->histories()->count() > $context->historyCountBefore) {
            /** @var PositionHistory $history */
            $history = $position->histories()->latest('id')->first();

            return $history;
        }

        if ($context->provisionalHistoryId !== null) {
            /** @var PositionHistory $history */
            $history = PositionHistory::query()->findOrFail($context->provisionalHistoryId);

            return $history;
        }

        return $this->createHistoryRecord($position);
    }

    private function consolidateProvisionalHistory(SaveContext $context, PositionHistory $finalHistory): void
    {
        if ($context->provisionalHistoryId === null || $context->provisionalHistoryId === $finalHistory->id) {
            return;
        }

        /** @var PositionHistory $provisionalHistory */
        $provisionalHistory = PositionHistory::query()->findOrFail($context->provisionalHistoryId);

        $this->moveMediaToHistory(
            $provisionalHistory->getMedia(self::COLLECTION),
            $finalHistory,
        );

        $provisionalHistory->delete();
    }

    /**
     * @param  Collection<int, Media>  $mediaBeforeSave
     * @return Collection<int, Media>
     */
    private function transferSupersededAfterSave(
        Position $position,
        Collection $mediaBeforeSave,
        PositionHistory $history,
    ): Collection {
        $currentMedia = $position->getMedia(self::COLLECTION);
        $beforeIds = $mediaBeforeSave->pluck('id');

        $newMedia = $currentMedia->whereNotIn('id', $beforeIds);

        if ($newMedia->isEmpty()) {
            return collect();
        }

        $supersededMedia = $currentMedia->whereIn('id', $beforeIds);

        return $this->moveMediaToHistory($supersededMedia, $history);
    }

    /**
     * @param  Collection<int, Media>  $mediaBeforeSave
     * @param  Collection<int, Media>  $currentMedia
     * @param  Collection<int, Media>  $movedRemovedMedia
     * @param  Collection<int, Media>  $movedSupersededMedia
     */
    private function recordAttachmentDiff(
        PositionHistory $history,
        Collection $mediaBeforeSave,
        Collection $currentMedia,
        Collection $movedRemovedMedia,
        Collection $movedSupersededMedia,
    ): void {
        $archivedMedia = $movedRemovedMedia
            ->merge($movedSupersededMedia)
            ->unique('id');

        $changedFields = $history->changed_fields;
        if (! is_array($changedFields)) {
            $changedFields = [];
        }

        $changedFields['position_attachments'] = [
            'from' => $this->formatMediaFileNames($mediaBeforeSave),
            'to' => $this->formatMediaFileNames($currentMedia),
        ];

        if ($archivedMedia->isNotEmpty() && $changedFields['position_attachments']['from'] === '') {
            $changedFields['position_attachments']['from'] = $this->formatMediaFileNames($archivedMedia);
        }

        $history->update([
            'changed_fields' => $changedFields === [] ? null : $changedFields,
        ]);
    }

    /**
     * @param  Collection<int, Media>  $media
     */
    private function formatMediaFileNames(Collection $media): string
    {
        return $media
            ->pluck('file_name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->join(', ');
    }

    private function createHistoryRecord(Position $position, ?array $changedFields = null): PositionHistory
    {
        return $position->histories()->create([
            'changed_by' => auth()->id(),
            'event_type' => 'updated',
            'snapshot' => Arr::except($position->toArray(), PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY),
            'changed_fields' => $changedFields,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field): array => [
                    $field->value => $field->isAffectedByDirty($changedFields ?? []),
                ])
                ->all(),
        ]);
    }
}

/**
 * @internal
 */
final class SaveContext
{
    /**
     * @param  list<string>  $keptMediaUuids
     */
    public function __construct(
        public readonly int $historyCountBefore,
        public readonly Collection $mediaBeforeSave,
        public readonly array $keptMediaUuids,
        public ?int $provisionalHistoryId = null,
        public Collection $movedRemovedMedia = new Collection,
    ) {}
}
