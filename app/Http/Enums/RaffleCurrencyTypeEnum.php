<?php

namespace App\Http\Enums;

enum RaffleCurrencyTypeEnum: int
{
    case FREE = 0;

    case VBUCKS = 1;

    case FIAT = 2;
}
