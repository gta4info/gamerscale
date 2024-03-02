<?php

namespace App\Http\Enums;

enum RaffleStatusEnum: int
{
    case PENDING = 0;

    case ACTIVE = 1;

    case COMPLETED = 2;
}
