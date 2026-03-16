<?php

namespace App\Enums;

enum EnumsDepartmentColor: string
{
    case RED = 'Red';
    case GREEN = 'Green';
    case BLUE = 'Blue';
    case YELLOW = 'Yellow';
    case PURPLE = 'Purple';
    case ORANGE = 'Orange';
    case PINK = 'Pink';
    case TEAL = 'Teal';
    case CYAN = 'Cyan';
    case LIME = 'Lime';
    case INDIGO = 'Indigo';
    case VIOLET = 'Violet';
    case EMERALD = 'Emerald';
    case FUCHSIA = 'Fuchsia';
    case ROSE = 'Rose';
    case SLATE = 'Slate';

    public function label(): string
    {
        return match ($this) {
            self::RED => 'Red',
            self::GREEN => 'Green',
            self::BLUE => 'Blue',
            self::YELLOW => 'Yellow',
            self::PURPLE => 'Purple',
            self::ORANGE => 'Orange',
            self::PINK => 'Pink',
            self::TEAL => 'Teal',
            self::CYAN => 'Cyan',
            self::LIME => 'Lime',
            self::INDIGO => 'Indigo',
            self::VIOLET => 'Violet',
            self::EMERALD => 'Emerald',
            self::FUCHSIA => 'Fuchsia',
            self::ROSE => 'Rose',
            self::SLATE => 'Slate',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RED => 'red',
            self::GREEN => 'green',
            self::BLUE => 'blue',
            self::YELLOW => 'yellow',
            self::PURPLE => 'purple',
            self::ORANGE => 'orange',
            self::PINK => 'pink',
            self::TEAL => 'teal',
            self::CYAN => 'cyan',
            self::LIME => 'lime',
            self::INDIGO => 'indigo',
            self::VIOLET => 'violet',
            self::EMERALD => 'emerald',
            self::FUCHSIA => 'fuchsia',
            self::ROSE => 'rose',
            self::SLATE => 'slate',
        };
    }
}
