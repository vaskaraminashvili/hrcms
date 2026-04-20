<?php

namespace App\Enums;

enum LanguageProficiency: string
{
    case A1 = 'A1';
    case A2 = 'A2';
    case B1 = 'B1';
    case B2 = 'B2';
    case C1 = 'C1';
    case C2 = 'C2';

    public function getLabel(): string
    {
        return match ($this) {
            self::A1 => 'A1',
            self::A2 => 'A2',
            self::B1 => 'B1',
            self::B2 => 'B2',
            self::C1 => 'C1',
            self::C2 => 'C2',
        };
    }

    /**
     * CEFR code with descriptive label (e.g. "A1: Beginner") for selects and UI.
     */
    public function getDisplayLabel(): string
    {
        return "{$this->value}: {$this->getLabel()}";
    }

    public function getColor(): string
    {
        return match ($this) {
            self::A1 => 'gray',
            self::A2 => 'primary',
            self::B1 => 'info',
            self::B2 => 'warning',
            self::C1 => 'success',
            self::C2 => 'danger',
        };
    }
}
