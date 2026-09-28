<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\PublicationScope;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Schemas\PersonalFile\PublicationsSchema;
use App\Jobs\SavePublicationDetailsJob;
use App\Models\Employee;
use App\Models\Publication;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Async;

/**
 * @property-read Schema $form
 */
class ClassifyPublications extends Page
{
    use InteractsWithRecord;

    protected static string $resource = EmployeeResource::class;

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    /**
     * Publication id => cache key for the background save.
     *
     * @var array<string, string>
     */
    public array $pendingPublicationSaves = [];

    /**
     * @var Collection<int, Publication>|null
     */
    protected ?Collection $publications = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $items = [];

        foreach ($this->publications() as $publication) {
            $details = is_array($publication->publication_details)
                ? $publication->publication_details
                : Publication::defaultPublicationDetails();

            $items[(string) $publication->getKey()] = [
                'scope' => $details['scope'] ?? PublicationScope::Local->value,
                'indexed' => (bool) ($details['indexed'] ?? false),
                'impact_factor' => (bool) ($details['impact_factor'] ?? false),
            ];
        }

        $this->form->fill([
            'items' => $items,
        ]);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->formComponents())
            ->columns(1);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form'),
            ]);
    }

    #[Async]
    public function savePublication(int $publicationId): void
    {
        $this->authorizeAccess();

        $publicationKey = (string) $publicationId;

        if (array_key_exists($publicationKey, $this->pendingPublicationSaves)) {
            return;
        }

        if (! $this->publications()->contains(
            fn (Publication $publication): bool => (int) $publication->getKey() === $publicationId,
        )) {
            Notification::make()
                ->title(__('filament.personal_file.publications.classify_save_failed'))
                ->danger()
                ->send();

            return;
        }

        $item = data_get($this->data, 'items.'.$publicationId);

        if (! is_array($item)) {
            Notification::make()
                ->title(__('filament.personal_file.publications.classify_save_failed'))
                ->danger()
                ->send();

            return;
        }

        $statusKey = 'publication-classification:'.Str::uuid()->toString();
        $this->pendingPublicationSaves[$publicationKey] = $statusKey;

        SavePublicationDetailsJob::dispatch(
            (int) $this->employee()->getKey(),
            $publicationId,
            Publication::normalizePublicationDetails(
                $item['scope'] ?? null,
                (bool) ($item['indexed'] ?? false),
                (bool) ($item['impact_factor'] ?? false),
            ),
            $statusKey,
        )->afterResponse();
    }

    public function refreshPublicationSaveStatus(): void
    {
        foreach (array_keys($this->pendingPublicationSaves) as $publicationId) {
            $statusKey = $this->pendingPublicationSaves[$publicationId] ?? null;

            if (! is_string($statusKey)) {
                continue;
            }

            $status = Cache::get($statusKey);

            if (! is_string($status)) {
                continue;
            }

            Cache::forget($statusKey);
            unset($this->pendingPublicationSaves[$publicationId]);

            if ($status === 'saved') {
                Notification::make()
                    ->title(__('filament.personal_file.publications.classify_saved'))
                    ->success()
                    ->send();

                continue;
            }

            Notification::make()
                ->title(__('filament.personal_file.publications.classify_save_failed'))
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament.personal_file.publications.classify_heading');
    }

    public function getBreadcrumb(): string
    {
        return __('filament.personal_file.publications.classify');
    }

    /**
     * @return array<int, Section|Text>
     */
    private function formComponents(): array
    {
        $publications = $this->publications();

        if ($publications->isEmpty()) {
            return [
                Text::make(__('filament.personal_file.publications.classify_empty')),
            ];
        }

        return [
            Section::make()
                ->contained(false)
                ->divided()
                ->extraAttributes(function (): array {
                    if ($this->pendingPublicationSaves === []) {
                        return [];
                    }

                    return ['wire:poll.2s' => 'refreshPublicationSaveStatus'];
                }, merge: true)
                ->schema($publications
                    ->map(fn (Publication $publication): Flex => $this->publicationRow($publication))
                    ->all()),
        ];
    }

    private function publicationRow(Publication $publication): Flex
    {
        $id = (int) $publication->getKey();

        return Flex::make([
            Group::make([
                Text::make($this->publicationLabel($publication))
                    ->weight(FontWeight::SemiBold),
                ...PublicationsSchema::classificationFields('items.'.$id),
            ])->grow(),
            View::make('filament.resources.employees.pages.classify-publication-save')
                ->grow(false)
                ->viewData(function () use ($id): array {
                    return [
                        'publicationId' => $id,
                        'isSaving' => array_key_exists((string) $id, $this->pendingPublicationSaves),
                    ];
                }),
        ])
            ->key('publication-'.$id)
            ->alignment(Alignment::Between);
    }

    /**
     * @return Collection<int, Publication>
     */
    private function publications(): Collection
    {
        if ($this->publications instanceof Collection) {
            return $this->publications;
        }

        return $this->publications = $this->employee()
            ->publications()
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    private function employee(): Employee
    {
        $record = $this->getRecord();

        abort_unless($record instanceof Employee, 404);

        return $record;
    }

    private function publicationLabel(Publication $publication): string
    {
        $title = trim((string) $publication->getTranslation('title', app()->getLocale()));

        if ($title === '') {
            $title = '#'.$publication->getKey();
        }

        if ($publication->published_at === null) {
            return $title;
        }

        return $title.' ('.$publication->published_at.')';
    }
}
