<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Imports\TrainingsSeminarsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TrainingsSeminarsSchema
{
    use HasTranslatableFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/trainings_seminars/trainings_seminars.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'trainings_seminars.xlsx';

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadTrainingsSeminarsTemplate')
                ->label(__('filament.personal_file.trainings_seminars.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importTrainingsSeminars')
                ->label(__('filament.personal_file.trainings_seminars.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.trainings_seminars.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.trainings_seminars.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.trainings_seminars.import_file_label'))
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

                    Excel::import(new TrainingsSeminarsImport($record->getKey()), $path);

                    $record->unsetRelation('trainingsSeminars');

                    Notification::make()
                        ->title(__('filament.personal_file.trainings_seminars.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['trainingsSeminars']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('institution', __('filament.personal_file.trainings_seminars.institution')),
            static::translatableField('topic', __('filament.personal_file.trainings_seminars.topic')),
            DatePicker::make('started_at')->label(__('filament.personal_file.dates.started_at')),
            DatePicker::make('ended_at')->label(__('filament.personal_file.dates.ended_at')),
        ];
    }
}
