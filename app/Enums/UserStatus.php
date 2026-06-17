<?php

namespace App\Enums;

enum UserStatus: int
{
    case Active = 1;
    case Banned = 0;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
