<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\PersonalFile;
use App\Filament\Forms\Components\ConstrainedSpatieMediaLibraryFileUpload;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;

class OtherSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.other.title')),
            ConstrainedSpatieMediaLibraryFileUpload::make('documents')
                ->label(__('filament.personal_file.other.documents'))
                ->collection(PersonalFile::OTHER->mediaCollectionName())
                ->multiple()
                ->columnSpanFull()
                ->openable()
                ->downloadable()
                ->removeUploadedFileButtonPosition('right')
                ->extraAttributes(['class' => 'attachments-upload']),
        ];
    }
}
