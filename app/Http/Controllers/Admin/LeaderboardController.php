<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Enums\LeaderboardStatusEnum;
use App\Models\Leaderboard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class LeaderboardController extends Controller
{
    public function list(): Response
    {
        return Inertia::render('Admin/Leaderboards/List', [
            'leaderboards' => Leaderboard::withCount('users')->orderByDesc('id')->get(),
            'available_statuses' => $this->getStatuses(),
        ]);
    }


    public function view(Leaderboard $leaderboard): Response
    {
        return Inertia::render('Admin/Leaderboard/View', [
            'leaderboard' => $leaderboard->load([
                'users' => function($query) {
                    $query->orderByDesc('points');
                },
                'users.user'
            ]),
            'available_statuses' => $this->getStatuses(),
        ]);
    }

    public function renderCreateView(): Response
    {
        return Inertia::render('Admin/Leaderboard/Create', [
            'available_statuses' => $this->getStatuses(),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $leaderboard = Leaderboard::create();

        return response()->json([
            'id' => $leaderboard->id,
            'message' => 'Лидерборд успешно создан.'
        ]);
    }

    public function delete(Leaderboard $leaderboard): JsonResponse
    {
        try {
            DB::transaction(function () use ($leaderboard) {
                $leaderboard->users()->delete();
                $leaderboard->delete();
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при удалении лидерборда id: ' . $leaderboard->id);
            Log::error($exception->getMessage());
            Log::error(json_encode($leaderboard));

            return response()->json([
                'message' => 'Ошибка при удалении лидерборда'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Лидерборд успешно удален.'
        ]);
    }

    public function update(Leaderboard $leaderboard, Request $request): JsonResponse
    {
        try {
            $leaderboard->update([
                'status' => $request->post('leaderboard')['status']
            ]);

            return response()->json([
                'leaderboard' => $leaderboard,
                'message' => 'Лидерборд успешно обновлена.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении лидерборда.');
            Log::error($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении лидерборда.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateUsers(Leaderboard $leaderboard, Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use($leaderboard, $request) {
                foreach ($request->post('users') as $item) {
                    if(!isset($item['id'])) { // Assign new user to leaderboard
                        $leaderboard->users()->create([
                            'user_id' => $item['user']['id'],
                            'points' => $item['points'],
                        ]);
                    } else { // Update stats if changed
                        $existed = $leaderboard->users()->where('id', '=', $item['id'])->first();
                        if($existed->points !== $item['points']) {
                            $existed->update(['points' => $item['points']]);
                        }
                    }
                }

                foreach ($request->post('usersToDelete') as $userId) {
                    $leaderboard->users()->where('user_id', '=', $userId)->delete();
                }
            });

            return response()->json([
                'leaderboard' => $leaderboard,
                'message' => 'Статистика игроков успешно обновлена.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении статистики игроков.');
            Log::error($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении статистики игроков.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }


    public function getStatuses(): array
    {
        $statuses = [];

        foreach (LeaderboardStatusEnum::cases() as $case) {
            $statuses[] = [
                'value' => $case->value,
                'title' => $case->name
            ];
        }

        return $statuses;
    }
}
