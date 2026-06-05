<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Imports\ScientificForumsImport;
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

class ScientificForumsSchema
{
    use HasTranslatableFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/scientific_forums/scientific_forums.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'scientific_forums.xlsx';

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadScientificForumsTemplate')
                ->label(__('filament.personal_file.scientific_forums.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importScientificForums')
                ->label(__('filament.personal_file.scientific_forums.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.scientific_forums.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.scientific_forums.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.scientific_forums.import_file_label'))
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

                    Excel::import(new ScientificForumsImport($record->getKey()), $path);

                    $record->unsetRelation('scientificForums');

                    Notification::make()
                        ->title(__('filament.personal_file.scientific_forums.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['scientificForums']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.scientific_forums.title')),
            static::translatableField('participation_form', __('filament.personal_file.scientific_forums.participation_form')),
            DatePicker::make('start_date')->label(__('filament.personal_file.dates.started_at')),
            DatePicker::make('end_date')->label(__('filament.personal_file.dates.ended_at')),
        ];
    }
}
