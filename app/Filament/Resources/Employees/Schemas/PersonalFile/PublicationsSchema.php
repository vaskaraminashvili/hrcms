<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Imports\PublicationsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicationsSchema
{
    use HasTranslatableFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/publications/scholar_export.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'scholar_export.xlsx';

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadPublicationsTemplate')
                ->label(__('filament.personal_file.publications.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importPublications')
                ->label(__('filament.personal_file.publications.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.publications.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.publications.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.publications.import_file_label'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            // Some servers detect .xlsx as zip or octet-stream (Office files are ZIP-based).
                            'application/zip',
                            'application/octet-stream',
                        ])
                        ->required(),
                ])
                ->visible(fn (?Model $record): bool => $record !== null)
                ->authorize('update')
                ->action(function (array $data, $livewire): void {
                    $record = $livewire->getRecord();
                    $file = $data['file'];

                    $path = $file instanceof TemporaryUploadedFile
                        ? $file->getRealPath()
                        : $file;

                    Excel::import(new PublicationsImport($record->getKey()), $path);

                    $record->unsetRelation('publications');

                    Notification::make()
                        ->title(__('filament.personal_file.publications.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['publications']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.publications.title')),
            static::translatableField('place', __('filament.personal_file.publications.place')),
            static::translatableField('co_authors', __('filament.personal_file.publications.co_authors')),
            Select::make('published_at')
                ->options(
                    collect(range(date('Y') - 50, date('Y') + 5))
                        ->mapWithKeys(fn ($year) => [$year => $year])
                        ->toArray()
                )
                ->default(date('Y'))
                ->searchable()
                ->label(__('filament.personal_file.dates.published_at')),
            TextInput::make('page_count')
                ->label(__('filament.personal_file.page_count'))
                ->numeric(),
        ];
    }
}
