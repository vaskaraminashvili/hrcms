<?php

namespace App\Enums;

enum Education: string
{
    case BACHELOR = 'Bachelor'; // ბაკალავრი
    case MASTER = 'Master'; // მაგისტრი
    case PHD = 'PhD'; // დოქტორი
    case ASSOCIATE = 'Associate'; // დამხმარე მასწავლებელი

    public function label(): string
    {
        return match ($this) {
            self::BACHELOR => 'ბაკალავრი',
            self::MASTER => 'მაგისტრი',
            self::PHD => 'დოქტორი',
            self::ASSOCIATE => 'დამხმარე მასწავლებელი',
        };
    }
}
