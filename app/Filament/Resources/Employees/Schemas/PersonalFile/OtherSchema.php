<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\PersonalFile;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class OtherSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.other.title')),
            SpatieMediaLibraryFileUpload::make('documents')
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
