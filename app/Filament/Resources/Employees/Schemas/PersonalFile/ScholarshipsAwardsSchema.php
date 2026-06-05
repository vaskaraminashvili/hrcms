<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Imports\ScholarshipsAwardsImport;
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

class ScholarshipsAwardsSchema
{
    use HasTranslatableFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/scholarships_awards/scholarships_awards.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'scholarships_awards.xlsx';

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadScholarshipsAwardsTemplate')
                ->label(__('filament.personal_file.scholarships_awards.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importScholarshipsAwards')
                ->label(__('filament.personal_file.scholarships_awards.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.scholarships_awards.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.scholarships_awards.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.scholarships_awards.import_file_label'))
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

                    Excel::import(new ScholarshipsAwardsImport($record->getKey()), $path);

                    $record->unsetRelation('scholarshipsAwards');

                    Notification::make()
                        ->title(__('filament.personal_file.scholarships_awards.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['scholarshipsAwards']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.scholarships_awards.title')),
            static::translatableField('issuer', __('filament.personal_file.scholarships_awards.issuer')),
            static::translatableField('grant_details', __('filament.personal_file.scholarships_awards.grant_details')),
            DatePicker::make('issued_at')->label(__('filament.personal_file.dates.issued_at')),
        ];
    }
}
