<?php

namespace App\Enums;

enum CvLocale: string
{
    case Georgian = 'ka';
    case English = 'en';

    public static function tryFromRoute(string $locale): ?self
    {
        return self::tryFrom($locale);
    }
}
