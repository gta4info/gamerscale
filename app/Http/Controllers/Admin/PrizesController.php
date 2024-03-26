<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PrizeUserController;
use App\Http\Enums\PrizeStatusEnum;
use App\Http\Enums\PrizeTypeEnum;
use App\Models\Prize;
use App\Models\PrizeUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PrizesController extends Controller
{
    public function list(): Response
    {
        return Inertia::render('Admin/Prizes/List', [
            'prizes' => Prize::all(),
            'available_types' => $this->getTypes()
        ]);
    }
    public function view(Prize $prize): Response
    {
        return Inertia::render('Admin/Prize/View', [
            'prize' => $prize,
            'available_types' => $this->getTypes(),
        ]);
    }

    public function delete(Prize $prize): JsonResponse
    {
        try {
            DB::transaction(function () use ($prize) {
                $prize->achievementPrizeToUser()->detach();
                $prize->achievements()->detach();
                $prize->delete();
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при удалении приза:');
            Log::error($exception->getMessage());
            Log::error(json_encode($prize));

            return response()->json([
                'message' => 'Ошибка при удалении приза'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Приз успешно удален.'
        ]);
    }

    public function getTypes(): array
    {
        $types = [];

        foreach (PrizeTypeEnum::cases() as $case) {
            $types[] = [
                'value' => $case->value,
                'title' => $case->name
            ];
        }

        return $types;
    }

    public function getStatuses(): array
    {
        $types = [];

        foreach (PrizeStatusEnum::cases() as $case) {
            $types[] = [
                'value' => $case->value,
                'title' => $case->name
            ];
        }

        return $types;
    }

    public function renderCreateView(): Response
    {
        return Inertia::render('Admin/Prize/Create', [
            'available_types' => $this->getTypes()
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $this->validation($request);

        $data = $request->all();

        try {
            if($request->file('icon')) {
                $data['icon'] = (new Controller())->uploadImage($request->file('icon'), 'prizes');
            }

            $prize = Prize::create($data);

            return response()->json([
                'id' => $prize->id,
                'message' => 'Приз успешно создан.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при создании приза.');
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при создании приза.'
            ]);
        }
    }

    public function update(Prize $prize, Request $request): JsonResponse
    {
        $this->validation($request);

        $data = $request->all();
        $updates = [];

        try {
            if($request->file('icon')) {
                $data['icon'] = (new Controller())->uploadImage($request->file('icon'), 'prizes');
            }

            foreach ($data as $k => $v) {
                if($prize->$k != $v) {
                    $updates[$k] = $v;
                }
            }

            $prize->update($updates);

            return response()->json([
                'prize' => $prize,
                'message' => 'Приз успешно обновлен.'
            ]);
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении приза.');
            Log::error($exception->getMessage());
            Log::debug(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении приза.'
            ]);
        }
    }

    public function validation(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:4|max:255',
            'value' => 'required|min:1',
            'type' => [new Enum(PrizeTypeEnum::class)],
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }
    }

    public function history(): Response
    {
        return Inertia::render('Admin/Prizes/History', [
            'prizes' => PrizeUser::with(['user', 'prize'])
                ->orderByDesc('id')
                ->get()
                ->each(function ($item) {
                    $item->parent = $item->parent();
                    $item->category = $this->getCategories()[$item->prizable_type];
                }),
            'statuses' => $this->getStatuses(),
            'categories' => $this->getCategories()
        ]);
    }

    public function historyViewPrize(PrizeUser $prize): Response
    {
        $prize->parent = $prize->parent();
        $prize->category = $this->getCategories()[$prize->prizable_type];
        $prize->status = (new PrizeUserController())->resolvePrizeStatus($prize->status);
        $prize->cardText = (new PrizeUserController())->resolvePrizeCardText($prize);

        $prizeData = [];
        foreach ($prize->data as $key => $val) {
            $prizeData[$key] = $val;
        }
        if(!isset($prizeData['comment'])) {
            $prizeData['comment'] = '';
            $prize->data = $prizeData;
        }

        return Inertia::render('Admin/Prizes/HistoryView', [
            'prize' => $prize->load(['user','prize']),
            'statuses' => $this->getStatuses(),
        ]);
    }

    public function historyUpdatePrize(PrizeUser $prize, Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use($prize, $request) {
                $updates = [];

                if($prize->status !== $request->post('status')) {
                    $updates['status'] = $request->post('status');
                }

                if(
                    !isset($prize->data['comment'])
                    || $prize->data['comment'] !== $request->post('comment')
                ) {
                    $updates['data->comment'] = $request->post('comment');
                }

                $prize->update($updates);
            });
        } catch (\Exception $exception) {
            Log::error('Ошибка при обновлении истории выдачи приза id: '.$prize->id);
            Log::error($exception->getMessage());
            Log::error(json_encode($request->all()));

            return response()->json([
                'message' => 'Ошибка при обновлении истории выдачи приза.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Информация успешно обновлена.'
        ]);
    }

    public function getCategories(): array
    {
        return [
            'App\Models\Achievement' => [
                'value' => 0,
                'title' => 'Ачивки'
            ],
            'App\Models\Quest' => [
                'value' => 1,
                'title' => 'Квесты'
            ],
            'App\Models\Tournament' => [
                'value' => 2,
                'title' => 'Турниры'
            ],
        ];
    }
}
