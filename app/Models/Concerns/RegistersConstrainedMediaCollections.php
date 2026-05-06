<?php

namespace App\Models\Concerns;

use Spatie\MediaLibrary\MediaCollections\MediaCollection;

trait RegistersConstrainedMediaCollections
{
    protected function registerConstrainedMediaCollection(string $name): MediaCollection
    {
        return $this->addMediaCollection($name)
            ->acceptsMimeTypes(config('media-library.allowed_mime_types'));
    }
}
