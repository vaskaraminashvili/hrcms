<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ConstrainedSpatieMediaLibraryFileUpload extends SpatieMediaLibraryFileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $maxKilobytes = (int) floor(config('media-library.max_file_size') / 1024);

        $this->acceptedFileTypes(config('media-library.allowed_mime_types'))
            ->maxSize(max($maxKilobytes, 1));
    }
}
