<?php

namespace App\Enums;

enum AcademicPosition: string
{
    case ASSISTANT = 'ASSISTANT';
    case ASSOCIATED = 'ASSOCIATED';
    case PROFESSOR = 'PROFESSOR';

    public function getLabel(): string
    {
        return match ($this) {
            self::ASSISTANT => 'ასისტენტ პროფესორი',
            self::ASSOCIATED => 'ასოცირებული პროფესორი',
            self::PROFESSOR => 'პროფესორი',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ASSISTANT => 'primary',
            self::ASSOCIATED => 'secondary',
            self::PROFESSOR => 'success',
        };
    }
}
