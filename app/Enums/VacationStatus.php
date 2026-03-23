<?php

namespace App\Enums;

enum VacationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'მიმდინარე',
            self::Approved => 'დადასტურებული',
            self::Rejected => 'უარყოფილი',
            self::Cancelled => 'გათიშული',
        };
    }
}
