<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\PublicationScope;
use App\Enums\ScientificForumRole;
use App\Exports\FilledExcelTemplate;
use App\Exports\PersonalFileTemplateRows;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasYearMonthFields;
use App\Imports\ScientificForumsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScientificForumsSchema
{
    use HasTranslatableFields;
    use HasYearMonthFields;

    private const TEMPLATE_RELATIVE_PATH = 'templates/scientific_forums/scientific_forums.xlsx';

    private const TEMPLATE_DOWNLOAD_NAME = 'scientific_forums.xlsx';

    private const FILLED_TEMPLATE_DOWNLOAD_NAME = 'scientific_forums_filled.xlsx';

    public static bool $fileUploadEnabled = true;

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
            Action::make('downloadFilledScientificForumsTemplate')
                ->label(__('filament.personal_file.scientific_forums.download_filled_template'))
                ->icon(Heroicon::ArrowDownOnSquare)
                ->visible(fn (?Model $record): bool => $record !== null)
                ->authorize('importPersonalFile')
                ->action(function ($livewire): BinaryFileResponse {
                    $record = $livewire->getRecord();

                    return FilledExcelTemplate::download(
                        resource_path(self::TEMPLATE_RELATIVE_PATH),
                        self::FILLED_TEMPLATE_DOWNLOAD_NAME,
                        $record->scientificForums,
                        PersonalFileTemplateRows::scientificForum(...),
                    );
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
                ->authorize('importPersonalFile')
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
            Section::make()
                ->schema([
                    Select::make('participation_role')
                        ->label(__('filament.personal_file.scientific_forums.participation_role'))
                        ->options(collect(ScientificForumRole::cases())->mapWithKeys(
                            fn (ScientificForumRole $case) => [$case->value => $case->getLabel()]
                        ))
                        ->native(false)
                        ->live()
                        ->afterStateHydrated(function (Select $component, mixed $state, Set $set): void {
                            $value = self::roleValue($state);

                            if ($value === null || ScientificForumRole::tryFrom($value) !== null) {
                                return;
                            }

                            $set('participation_role_other', $value);
                            $component->state(ScientificForumRole::Other->value);
                        })
                        ->dehydrateStateUsing(function (mixed $state, Get $get): ?string {
                            $value = self::roleValue($state);

                            if ($value === ScientificForumRole::Other->value) {
                                $other = $get('participation_role_other');

                                return filled($other) ? trim((string) $other) : null;
                            }

                            return $value;
                        }),
                    TextInput::make('participation_role_other')
                        ->label(__('filament.personal_file.scientific_forums.participation_role_other'))
                        ->visible(fn (Get $get): bool => self::roleValue($get('participation_role')) === ScientificForumRole::Other->value)
                        ->required(fn (Get $get): bool => self::roleValue($get('participation_role')) === ScientificForumRole::Other->value)
                        ->dehydrated(false),
                    Radio::make('scope')
                        ->label(__('filament.personal_file.scientific_forums.scope'))
                        ->options(PublicationScope::class)
                        ->default(PublicationScope::Local->value)
                        ->inline()
                        ->required()
                        ->afterStateHydrated(function (Radio $component, mixed $state): void {
                            if (blank($state)) {
                                $component->state(PublicationScope::Local->value);
                            }
                        }),
                ])
                ->columnSpanFull(),
            static::translatableField('participation_form', __('filament.personal_file.scientific_forums.participation_form')),
            static::yearMonthField('start_date', __('filament.personal_file.dates.started_at')),
            static::yearMonthField('end_date', __('filament.personal_file.dates.ended_at')),
        ];
    }

    public static function fileUploadEnabled(): bool
    {
        return self::$fileUploadEnabled;
    }

    private static function roleValue(mixed $state): ?string
    {
        if ($state instanceof ScientificForumRole) {
            return $state->value;
        }

        if (! is_string($state) || $state === '') {
            return null;
        }

        return $state;
    }
}
