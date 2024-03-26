<?php

namespace App\Http\Controllers;

use App\Http\Enums\PrizeStatusEnum;

class PrizeUserController extends Controller
{
    public function resolvePrizeStatus(int $status): array
    {
        return match ($status) {
            PrizeStatusEnum::IN_PROGRESS->value => ['value' => $status, 'text' => 'В процессе выдачи', 'class' => 'in-progress'],
            PrizeStatusEnum::COMPLETED->value => ['value' => $status, 'text' => 'Получен', 'class' => 'completed'],
            default => ['value' => $status, 'text' => 'В ожидании', 'class' => 'pending'],
        };
    }

    public function resolvePrizeCardText($prize): string
    {
        return match($prize->prizable_type) {
            'App\Models\Achievement' => "Получен за достижение <strong>{$prize->data['level']} уровня</strong> в ачивке <strong>\"{$prize->parent()->first()->title}\"</strong>",
            default => 'Автоматическая выдача приза'
        };
    }
}
