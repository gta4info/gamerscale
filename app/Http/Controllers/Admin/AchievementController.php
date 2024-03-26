<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Enums\AchievementTypeEnum;
use App\Http\Enums\PrizeTypeEnum;
use App\Models\Achievement;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AchievementController extends Controller
{
    public function list(): Response
    {
        return Inertia::render('Admin/Achievements/List', [
            'achievements' => Achievement::orderByDesc('id')->get(),
            'available_types' => $this->getTypes()
        ]);
    }

    public function view(Achievement $achievement): Response
    {
        $levels = [];

        foreach ($achievement->levels as $key => $value) {
            $levels[] = [
                'level' => $key+1,
                'value' => $value,
                'prizes' => $achievement->prizes()
                    ->select(['id', 'prize_id', 'achievement_id'])
                    ->where('prize_achievement.level', '=', $key+1)
                    ->with(['prize' => function($query) {
                        $query->select(['id', 'type', 'value', 'icon']);
                    }])
                    ->get(),
            ];
        }

        return Inertia::render('Admin/Achievement/View', [
            'achievement' => $achievement,
            'levels' => $levels,
            'available_types' => $this->getTypes(),
            'prizes' => Prize::all()->each(function ($prize) {
                $prize->type = PrizeTypeEnum::from($prize->type)->name;
            })
        ]);
    }

    public function delete(Achievement $achievement): JsonResponse
    {
        try {
            DB::transaction(function () use ($achievement) {
                $achievement->userPrizes()->delete();
                $achievement->prizes()->delete();
                $achievement->delete();
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при удалении ачивки:');
            Log::error($exception->getMessage());
            Log::error(json_encode($achievement));

            return response()->json([
                'message' => 'Ошибка при удалении ачивки'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Ачивка успешно удалена.'
        ]);
    }


    public function renderCreateView(): Response
    {
        return Inertia::render('Admin/Achievement/Create', [
            'available_types' => $this->getTypes(),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $this->validation($request);

        $data = $request->all();

        try {
            if($request->file('icon')) {
                $data['icon'] = (new Controller())->uploadImage($request->file('icon'), 'achievements');
            }

            $achievement = Achievement::create($data);

            return response()->json([
                'id' => $achievement->id,
                'message' => 'Ачивка успешно создана.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при создании ачивки.');
            Log::error($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при создании ачивки.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function update(Achievement $achievement, Request $request): JsonResponse
    {
        $this->validation($request);

        $data = $request->all();
        $updates = [];

        try {
            if($request->file('icon')) {
                $data['icon'] = (new Controller())->uploadImage($request->file('icon'), 'achievements');
            }

            if($request->post('is_active')) {
                $data['is_active'] = !($request->post('is_active') == 'false');
            }

            foreach ($data as $k => $v) {
                if($achievement->$k != $v) {
                    $updates[$k] = $v;
                }
            }

            $achievement->update($updates);

            return response()->json([
                'achievement' => $achievement,
                'message' => 'Ачивка успешно обновлена.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении ачивки.');
            Log::error($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении ачивки.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getTypes(): array
    {
        $types = [];

        foreach (AchievementTypeEnum::cases() as $case) {
            $types[] = [
                'value' => $case->value,
                'title' => $case->name
            ];
        }

        return $types;
    }

    public function validation(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:4|max:255',
            'description' => 'required|min:4|max:255',
            'type' => [new Enum(AchievementTypeEnum::class)],
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY)
            );
        }
    }

    public function updateLevels(Achievement $achievement, Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use($achievement, $request) {
                // Collect all levels values sorted by level
                $levelsValues = collect($request->post('levels'))
                    ->sortBy('level')
                    ->pluck('value');

                // Update levels array on achievement model
                $achievement->update(['levels' => $levelsValues]);

                // Remove all prizes by levels for achievement
                $achievement->prizes()->delete();

                // Create new records with level and its prizes for each level
                foreach ($request->post('levels') as $level) {
                    foreach ($level['prizes'] as $prize) {
                        $achievement->prizes()->create([
                            'level' => $level['level'],
                            'prize_id' => $prize['prize_id']
                        ]);
                    }
                }
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении ачивки.');
            Log::debug($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении уровней ачивки.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Уровни успешно обновлены'
        ]);
    }

    public function assignAchievementsToUser(User $user): void
    {
        $achievements = Achievement::select('id')
            ->where('is_active', '=', true)
            ->get()
            ->pluck('id');

        foreach ($achievements as $id) {
            if($user->achievements()->where('achievement_id', '=', $id)->count() === 0) {
                $user->achievements()->create([
                    'achievement_id' => $id
                ]);
            }
        }
    }
}
