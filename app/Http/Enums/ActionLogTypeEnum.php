<?php

namespace App\Http\Enums;

enum ActionLogTypeEnum: int
{
    case BALANCE_CHANGE = 0;
    case PRIZE_ASSIGNED = 1;
    case ACHIEVEMENT_STATS_CHANGE = 2;
    case LEADERBOARD_STATS_CHANGE = 3;
}
