<?php

namespace App\Enums;

enum AcademicDegree: string
{
    case DOCTOR = 'DOCTOR';
    case MAGISTER = 'MAGISTER';
    case OTHER = 'OTHER';

    public function getLabel(): string
    {
        return match ($this) {
            self::DOCTOR => 'დოქტორი',
            self::MAGISTER => 'მაგისტრი',
            self::OTHER => 'სხვა',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DOCTOR => 'primary',
            self::MAGISTER => 'secondary',
            self::OTHER => 'gray',
        };
    }
}
