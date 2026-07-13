<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasYearMonthFields;
use App\Imports\WorkExperienceImport;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkExperienceSchema
{
    use HasTranslatableFields;
    use HasYearMonthFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/work_experience/work_experience.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'work_experience.xlsx';

    public static bool $fileUploadEnabled = false;

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadWorkExperienceTemplate')
                ->label(__('filament.personal_file.work_experience.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importWorkExperience')
                ->label(__('filament.personal_file.work_experience.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.work_experience.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.work_experience.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.work_experience.import_file_label'))
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/zip',
                            'application/octet-stream',
                        ])
                        ->required(),
                ])
                ->visible(fn (?Model $record): bool => $record !== null)
                ->authorize('importPersonalFile')
                ->action(function (array $data, $livewire): void {
                    $record = $livewire->getRecord();
                    $file = $data['file'];

                    $path = $file instanceof TemporaryUploadedFile
                        ? $file->getRealPath()
                        : $file;

                    Excel::import(new WorkExperienceImport($record->getKey()), $path);

                    $record->unsetRelation('workExperiences');

                    Notification::make()
                        ->title(__('filament.personal_file.work_experience.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['workExperiences']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('institution', __('filament.personal_file.work_experience.institution')),
            static::translatableField('position', __('filament.personal_file.work_experience.position')),
            static::yearMonthField('started_at', __('filament.personal_file.dates.started_at'))
                ->live()
                ->afterStateUpdated(function (Get $get, Set $set): void {
                    $endedAt = $get('ended_at');
                    $startedAt = $get('started_at');

                    if (blank($endedAt) || blank($startedAt)) {
                        return;
                    }

                    if (Carbon::parse($endedAt)->startOfMonth()->lte(Carbon::parse($startedAt)->startOfMonth())) {
                        $set('ended_at', null);
                    }
                }),
            static::yearMonthField('ended_at', __('filament.personal_file.dates.ended_at'))
                ->disabled(fn (Get $get): bool => blank($get('started_at')))
                ->extraInputAttributes(fn (Get $get): array => filled($get('started_at'))
                    ? ['min' => Carbon::parse($get('started_at'))->addMonth()->format('Y-m')]
                    : []),
        ];
    }

    public static function fileUploadEnabled(): bool
    {
        return self::$fileUploadEnabled;
    }
}
