<?php

namespace App\Enums;

enum LanguageProficiency: string
{
    case BEGINNER = 'BEGINNER';
    case INTERMEDIATE = 'INTERMEDIATE';
    case ADVANCED = 'ADVANCED';
    case NATIVE = 'NATIVE';



    public function getLabel(): string
    {
        return match ($this) {
            self::BEGINNER => 'დამწყები',
            self::INTERMEDIATE => 'საშუალოდ',
            self::ADVANCED => 'უმაღლესი',
            self::NATIVE => 'როგორც მშობლიური',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BEGINNER => 'primary',
            self::INTERMEDIATE => 'secondary',
            self::ADVANCED => 'success',
            self::NATIVE => 'danger',
        };
}
