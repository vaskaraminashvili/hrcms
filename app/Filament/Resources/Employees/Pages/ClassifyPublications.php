<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Enums\PublicationScope;
use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\Schemas\PersonalFile\PublicationsSchema;
use App\Models\Employee;
use App\Models\Publication;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

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
     * @var Collection<int, Publication>|null
     */
    protected ?Collection $publications = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->form->fill([
            'scope' => PublicationScope::Local->value,
            'indexed' => false,
            'impact_factor' => false,
            'publication_ids' => [],
        ]);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...PublicationsSchema::classificationFields(''),
                CheckboxList::make('publication_ids')
                    ->label(__('filament.personal_file.publications.classify_list'))
                    ->options(fn (): array => $this->publicationOptions())
                    ->descriptions(fn (): array => $this->publicationDescriptions())
                    ->helperText(fn (): ?string => $this->publications()->isEmpty()
                        ? __('filament.personal_file.publications.classify_empty')
                        : null)
                    ->searchable()
                    ->bulkToggleable()
                    ->required(),
            ])
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
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('filament.personal_file.publications.classify_submit'))
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $details = Publication::normalizePublicationDetails(
            $data['scope'] ?? null,
            (bool) ($data['indexed'] ?? false),
            (bool) ($data['impact_factor'] ?? false),
        );

        $updated = Publication::applyPublicationDetails(
            (int) $this->employee()->getKey(),
            $data['publication_ids'] ?? [],
            $details,
        );

        $this->publications = null;

        $this->form->fill([
            'scope' => $details['scope'],
            'indexed' => $details['indexed'],
            'impact_factor' => $details['impact_factor'],
            'publication_ids' => [],
        ]);

        Notification::make()
            ->title(__('filament.personal_file.publications.classify_success', ['count' => $updated]))
            ->success()
            ->send();
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
     * @return array<string, string>
     */
    private function publicationOptions(): array
    {
        return $this->publications()
            ->mapWithKeys(fn (Publication $publication): array => [
                (string) $publication->getKey() => $this->publicationLabel($publication),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function publicationDescriptions(): array
    {
        return $this->publications()
            ->mapWithKeys(fn (Publication $publication): array => [
                (string) $publication->getKey() => $this->publicationDescription($publication),
            ])
            ->all();
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

    private function publicationDescription(Publication $publication): string
    {
        $details = is_array($publication->publication_details) ? $publication->publication_details : [];
        $scope = PublicationScope::tryFrom((string) ($details['scope'] ?? '')) ?? PublicationScope::Local;
        $parts = [$scope->getLabel()];

        if ($scope === PublicationScope::International) {
            $indexed = (bool) ($details['indexed'] ?? false);
            $parts[] = __('filament.personal_file.publications.indexed').': '.$this->yesNo($indexed);

            if ($indexed) {
                $parts[] = __('filament.personal_file.publications.impact_factor').': '.$this->yesNo((bool) ($details['impact_factor'] ?? false));
            }
        }

        return implode(', ', $parts);
    }

    private function yesNo(bool $value): string
    {
        return $value
            ? __('filament.personal_file.publications.yes')
            : __('filament.personal_file.publications.no');
    }
}
