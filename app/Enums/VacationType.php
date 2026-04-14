<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VacationType: string implements HasLabel
{
    case VACATION = 'VACATION';
    case DAY_OFF = 'DAY_OFF';
    case MATERNITY_LEAVE = 'MATERNITY_LEAVE';
    case REMOTE_WORK = 'REMOTE_WORK';
    case SICK_LEAVE = 'SICK_LEAVE';
    case PERSONAL_LEAVE = 'PERSONAL_LEAVE';
    case OTHER = 'OTHER';

    public function getLabel(): string
    {
        return match ($this) {
            self::VACATION => 'შვებულება',
            self::DAY_OFF => 'დეიოფი',
            self::MATERNITY_LEAVE => 'დეკრეტული შვებულება',
            self::REMOTE_WORK => 'დისტანციური მუშაობა',
            self::SICK_LEAVE => 'საავადმყოფო ბარათი',
            self::PERSONAL_LEAVE => 'პირადი მიზეზი',
            self::OTHER => 'სხვა',
        };
    }
}
