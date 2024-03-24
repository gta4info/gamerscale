<?php

namespace App\Http\Enums;

enum AchievementPrizeUserStatusEnum: int
{
    case PENDING = 0;
    case WAITING_FOR_DETAILS = 1;
    case IN_PROGRESS = 2;
    case DETAILS_MISMATCH = 3;
    case REJECTED = 4;
    case COMPLETED = 5;
}
