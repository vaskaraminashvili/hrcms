<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\Education;
use App\Enums\EmployeeStatusEnum;
use App\Enums\Gender;
use App\Enums\PersonalFile;
use App\Filament\Resources\Employees\Schemas\PersonalFile\PublicationsSchema;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('filament.tabs.container'))
                    ->tabs([
                        Tab::make(__('filament.tabs.basic_information'))
                            ->schema([
                                Select::make('status')
                                    ->options(EmployeeStatusEnum::class)
                                    ->label(__('filament.status'))
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('employee_image')
                                    ->label(__('filament.employee_image'))
                                    ->collection('employee_image')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpanFull()
                                    ->extraAttributes(['class' => 'attachments-upload']),
                                TextInput::make('name')
                                    ->label(__('filament.name'))
                                    ->required(),
                                TextInput::make('surname')
                                    ->label(__('filament.surname'))
                                    ->required(),
                                TextInput::make('name_eng')
                                    ->label(__('filament.name_eng')),
                                TextInput::make('surrname_eng')
                                    ->label(__('filament.surrname_eng')),
                                TextInput::make('personal_number')
                                    ->label(__('filament.personal_number'))
                                    ->unique()
                                    ->length(11)
                                    ->required(),
                                TextInput::make('email')
                                    ->label(__('filament.email'))
                                    ->email(),
                                DatePicker::make('birth_date')
                                    ->label(__('filament.birth_date'))
                                    ->required(),
                                Select::make('gender')
                                    ->options(Gender::class)
                                    ->default(__('filament.gender_default'))
                                    ->label(__('filament.gender')),

                                Section::make()
                                    ->schema([
                                        Radio::make('education')
                                            ->label(__('filament.education'))
                                            ->options([
                                                1 => __('filament.education_level.secondary'),
                                                2 => __('filament.education_level.higher'),
                                            ])
                                            ->inline()
                                            ->live()
                                            ->required(),
                                        Select::make('degree')
                                            ->label(__('filament.degree'))
                                            ->options(Education::class)
                                            ->visible(fn ($get): bool => (int) $get('education') === 2)
                                            ->required(fn ($get): bool => (int) $get('education') === 2),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                                TextInput::make('mobile_number')
                                    ->label(__('filament.mobile_number')),
                                TextInput::make('account_number')
                                    ->label(__('filament.account_number')),
                                Section::make()
                                    ->schema([
                                        TextInput::make('citizenship')
                                            ->label(__('filament.citizenship'))
                                            ->columnSpanFull(),

                                        TextInput::make('address_details.address_physical')
                                            ->label(__('filament.address_physical')),
                                        TextInput::make('address_details.address_jurisdiction')
                                            ->label(__('filament.address_jurisdiction')),
                                        TextInput::make('address_details.en_address_physical')
                                            ->label(__('filament.en_address_physical')),
                                        TextInput::make('address_details.en_address_jurisdiction')
                                            ->label(__('filament.en_address_jurisdiction')),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('personal_file_attachments_attachments')
                                    ->label(__('filament.personal_file.attachments'))
                                    ->collection('basic_information_attachments')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpanFull()
                                    ->extraAttributes(['class' => 'attachments-upload']),

                            ])
                            ->columns(2),
                        ...array_map(
                            function (PersonalFile $case) {
                                $schemaClass = $case->schemaClass();

                                $tabSchema = [
                                    Repeater::make($case->relationship())
                                        ->label($case->label())
                                        ->default([])
                                        ->relationship()
                                        ->schema($schemaClass::schema())
                                        ->collapsed()
                                        ->collapsible()
                                        ->reorderable()
                                        ->columnSpanFull()
                                        ->afterLabel([
                                            Action::make('add_repeater_item_'.$case->value)
                                                ->label(__('filament.add_record_button'))
                                                ->button()
                                                ->color('warning')
                                                ->visible(fn (Repeater $component): bool => $component->isAddable())
                                                ->action(function (Repeater $component): void {
                                                    $newUuid = $component->generateUuid();

                                                    $items = $component->getRawState();

                                                    if ($newUuid) {
                                                        $items[$newUuid] = [];
                                                    } else {
                                                        $items[] = [];
                                                    }

                                                    $component->rawState($items);

                                                    $component->getChildSchema($newUuid ?? array_key_last($items))->fill();

                                                    $component->collapsed(false, shouldMakeComponentCollapsible: false);

                                                    $component->callAfterStateUpdated();

                                                    if ($component->shouldPartiallyRenderAfterActionsCalled()) {
                                                        $component->partiallyRender();
                                                    }
                                                }),
                                        ])
                                        ->addAction(fn (Action $action) => $action->visible(false))
                                        ->itemLabel(fn (array $state): ?string => $case->resolveItemLabelFromState($state)),
                                    SpatieMediaLibraryFileUpload::make('personal_file_attachments_'.$case->value)
                                        ->label(__('filament.personal_file.attachments'))
                                        ->collection($case->mediaCollectionName())
                                        ->removeUploadedFileButtonPosition('right')
                                        ->multiple()
                                        ->openable()
                                        ->downloadable()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'attachments-upload']),
                                ];

                                if ($case === PersonalFile::PUBLICATIONS) {
                                    array_unshift($tabSchema, PublicationsSchema::tabHeaderActions());
                                }

                                return Tab::make(__('filament.personal_file.tabs.'.$case->value))
                                    ->badge(fn ($record) => $record === null
                                        ? 0
                                        : $record->{$case->relationship()}()->count() + $record->getMedia($case->mediaCollectionName())->count())
                                    ->schema($tabSchema);
                            },
                            PersonalFile::cases()
                        ),
                    ])
                    ->activeTab(1)
                    ->vertical()
                    ->columnSpanFull(),
            ]);
    }
}
