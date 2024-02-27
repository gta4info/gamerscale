<?php

namespace App\Http\Enums;

enum UserBalanceTypeEnum: int
{
    case FIAT = 0;
    case VBUCKS = 1;
}
