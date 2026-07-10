<?php

namespace App\Enums;

enum AcademicDegree: string
{
    case DOCTOR = 'DOCTOR';
    case OTHER = 'OTHER';

    public function getLabel(): string
    {
        return match ($this) {
            self::DOCTOR => 'დოქტორი',
            self::OTHER => 'სხვა',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DOCTOR => 'primary',
            self::OTHER => 'gray',
        };
    }
}
