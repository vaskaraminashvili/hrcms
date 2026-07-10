<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Education: string implements HasLabel
{
    case BACHELOR = 'Bachelor'; // ბაკალავრი
    case MASTER = 'Master'; // მაგისტრი
    case PHD = 'PhD'; // დოქტორი

    public function getLabel(): string
    {
        return match ($this) {
            self::BACHELOR => 'ბაკალავრი',
            self::MASTER => 'მაგისტრი',
            self::PHD => 'დოქტორი',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
