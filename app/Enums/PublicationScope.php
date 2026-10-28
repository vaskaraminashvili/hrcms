<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PublicationScope: string implements HasLabel
{
    case Local = 'local';
    case International = 'international';

    public function getLabel(): string
    {
        return match ($this) {
            self::Local => __('filament.personal_file.publications.scope_options.local'),
            self::International => __('filament.personal_file.publications.scope_options.international'),
        };
    }
}
