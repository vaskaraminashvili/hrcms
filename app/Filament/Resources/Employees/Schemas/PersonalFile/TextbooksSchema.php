<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Imports\TextbooksImport;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TextbooksSchema
{
    use HasTranslatableFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/textbooks/textbooks.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'textbooks.xlsx';

    public static function tabHeaderActions(): Actions
    {
        return Actions::make([
            Action::make('downloadTextbooksTemplate')
                ->label(__('filament.personal_file.textbooks.download_template'))
                ->icon(Heroicon::ArrowDownTray)
                ->action(function (): BinaryFileResponse {
                    $path = resource_path(self::TEMPLATE_RELATIVE_PATH);

                    abort_unless(is_file($path), 404);

                    return response()->download($path, self::TEMPLATE_DOWNLOAD_NAME);
                }),
            Action::make('importTextbooks')
                ->label(__('filament.personal_file.textbooks.import'))
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading(__('filament.personal_file.textbooks.import_modal_heading'))
                ->modalSubmitActionLabel(__('filament.personal_file.textbooks.import_submit'))
                ->schema([
                    FileUpload::make('file')
                        ->label(__('filament.personal_file.textbooks.import_file_label'))
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

                    Excel::import(new TextbooksImport($record->getKey()), $path);

                    $record->unsetRelation('textbooks');

                    Notification::make()
                        ->title(__('filament.personal_file.textbooks.import_success'))
                        ->success()
                        ->send();

                    $livewire->refreshFormData(['textbooks']);
                }),
        ])->alignBetween();
    }

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.textbooks.title')),
            static::translatableField('publisher', __('filament.personal_file.textbooks.publisher')),
            static::translatableField('co_authors', __('filament.personal_file.textbooks.co_authors')),
            DatePicker::make('published_at')->label(__('filament.personal_file.dates.published_at')),
            TextInput::make('page_count')
                ->label(__('filament.personal_file.page_count'))
                ->numeric(),
        ];
    }
}
