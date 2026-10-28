<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasYearMonthFields;
use App\Imports\ScientificProjectsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScientificProjectsSchema
{
    use HasTranslatableFields;
    use HasYearMonthFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/scientific_projects/scientific_projects.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'scientific_projects.xlsx';

    public static bool $fileUploadEnabled = false;

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadScientificProjectsTemplate')
                ->label(__('filament.personal_file.scientific_projects.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importScientificProjects')
                ->label(__('filament.personal_file.scientific_projects.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.scientific_projects.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.scientific_projects.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.scientific_projects.import_file_label'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
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

                    Excel::import(new ScientificProjectsImport($record->getKey()), $path);

                    $record->unsetRelation('scientificProjects');

                    Notification::make()
                        ->title(__('filament.personal_file.scientific_projects.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['scientificProjects']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('project_name', __('filament.personal_file.scientific_projects.project_name')),
            static::translatableField('institution', __('filament.personal_file.scientific_projects.institution')),
            static::translatableField('position', __('filament.personal_file.scientific_projects.position')),
            static::yearMonthField('started_at', __('filament.personal_file.dates.started_at')),
            static::yearMonthField('ended_at', __('filament.personal_file.dates.ended_at')),
        ];
    }

    public static function fileUploadEnabled(): bool
    {
        return self::$fileUploadEnabled;
    }
}
