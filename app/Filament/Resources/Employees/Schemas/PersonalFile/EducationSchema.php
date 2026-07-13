<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasYearMonthFields;
use App\Imports\EducationImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EducationSchema
{
    use HasTranslatableFields;
    use HasYearMonthFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/education/education.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'education.xlsx';

    public static bool $fileUploadEnabled = true;

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadEducationTemplate')
                ->label(__('filament.personal_file.education.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importEducation')
                ->label(__('filament.personal_file.education.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.education.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.education.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.education.import_file_label'))
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

                    Excel::import(new EducationImport($record->getKey()), $path);

                    $record->unsetRelation('educations');

                    Notification::make()
                        ->title(__('filament.personal_file.education.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['educations']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('institution', __('filament.personal_file.education.institution')),
            static::translatableField('program', __('filament.personal_file.education.program')),
            static::translatableField('specialty', __('filament.personal_file.education.specialty')),
            static::yearMonthField('started_at', __('filament.personal_file.dates.started_at')),
            static::yearMonthField('ended_at', __('filament.personal_file.dates.ended_at')),
        ];
    }

    public static function fileUploadEnabled(): bool
    {
        return self::$fileUploadEnabled;
    }
}
