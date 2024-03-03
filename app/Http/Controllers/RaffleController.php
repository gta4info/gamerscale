<?php

namespace App\Http\Controllers;

use App\Http\Enums\RaffleCurrencyTypeEnum;
use App\Http\Enums\RaffleStatusEnum;
use App\Http\Enums\UserBalanceTypeEnum;
use App\Models\Raffle;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Nwilging\LaravelDiscordBot\Contracts\Services\DiscordInteractionServiceContract;

class RaffleController extends Controller
{
    private $interactionService;

    public function __construct(DiscordInteractionServiceContract $interactionService)
    {
        $this->interactionService = $interactionService;
    }

    public function handleDiscordInteraction(Request $request)
    {
        Log::info(json_encode($request->all()));

        $response = $this->interactionService->handleInteractionRequest($request);

        Log::info(json_encode($response));

        return response()->json($response->toArray(), $response->getStatus());
    }

    /**
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:4|max:255',
            'currency_type' => [new Enum(RaffleCurrencyTypeEnum::class)],
            'cost' => 'numeric|min:0',
            'winners_amount' => 'integer|min:1',
            'start_at' => 'required|date_format:Y-m-d H:i:s',
            'end_at' => 'required|date_format:Y-m-d H:i:s|after:start_at',
        ]);

        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        try {
            Raffle::create($request->all());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error(json_encode($request->all()));
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Розыгрыш создан.']);
    }

    public function update(Request $request, Raffle $raffle): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'min:4|max:255',
            'start_at' => 'date_format:Y-m-d H:i:s',
            'end_at' => 'date_format:Y-m-d H:i:s|after:start_at',
        ]);

        if($validator->fails()) {
            throw new HttpResponseException(
                response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        try {
            $raffle->fill($request->all())->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error('Raffle id: ' . $raffle->id);
            Log::error(json_encode($request->all()));
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Информация розыгрыша обновлена.']);
    }

    public function participate(Request $request, Raffle $raffle): JsonResponse
    {
        if($raffle->getStatus() === RaffleStatusEnum::COMPLETED->value) {
            throw new HttpResponseException(
                response()->json(['message' => 'Розыгрыш завершен.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        if($raffle->getStatus() === RaffleStatusEnum::PENDING->value) {
            throw new HttpResponseException(
                response()->json(['message' => 'Розыгрыш еще не начался.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $ticketsAmount = $request->post('tickets_amount', 1);
        $user = (new UserController())->firstOrCreate($request);

        try {
            /** Purchasing tickets if raffle is not free */
            if($raffle->currency_type !== RaffleCurrencyTypeEnum::FREE->value) {
                $this->purchaseTickets($raffle, $user, $ticketsAmount);
            } else {
                if($raffle->tickets()->where('user_id', '=', $user->id)->count() >= 1) {
                    throw new \Exception('Нельзя иметь больше 1 билета в бесплатном розыгрыше.');
                }

                $ticketsAmount = 1;
            }

            for($i = 1; $i <= $ticketsAmount; $i++) {
                $raffle->tickets()->create([
                    'user_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $totalTicketToUser = $raffle->tickets()
            ->where('user_id', '=', $user->id)
            ->count();

        return response()->json([
            'purchased_amount' => $ticketsAmount,
            'total_amount' => $totalTicketToUser,
            'winning_chance' => $totalTicketToUser / $raffle->tickets()->count() * 100
        ]);
    }

    /**
     * @throws \Exception
     */
    public function purchaseTickets(Raffle $raffle, User $user, int $ticketsAmount): void
    {
        $userBalanceController = new UserBalanceController();
        $userBalance = $userBalanceController->getCurrentBalanceByType($user, $raffle->currency_type);
        $amountToSpend = $raffle->cost * $ticketsAmount;

        if($userBalance < $amountToSpend) {
            throw new \Exception('Недостаточно средств для покупки билетов. На вашем счете: ' . $userBalance);
        }

        if($raffle->currency_type === RaffleCurrencyTypeEnum::VBUCKS->value) {
            $balanceType = UserBalanceTypeEnum::VBUCKS->value;
        } else {
            $balanceType = UserBalanceTypeEnum::FIAT->value;
        }

        $user->balance()->create([
            'amount' => $userBalance - $amountToSpend,
            'type' => $balanceType
        ]);
    }

    public function getUserSummaries(Request $request, Raffle $raffle): JsonResponse
    {
        $user = (new UserController())->firstOrCreate($request);
        $totalTicketToUser = $raffle->tickets()
            ->where('user_id', '=', $user->id)
            ->count();

        return response()->json([
            'tickets' => $totalTicketToUser,
            'winning_chance' => $totalTicketToUser / $raffle->tickets()->count() * 100
        ]);
    }

    public function draw(Raffle $raffle): void
    {
        for($i = 1; $i <= $raffle->winners_amount; $i++) {
            $winner_ticket_ids = $raffle->winner_ticket_ids ?? [];
            if(count($winner_ticket_ids) >= $raffle->winners_amount) {
                return;
            }

            /** Collect already won user ids */
            $winnerUserIds = $raffle->tickets
                ->whereIn('id', $winner_ticket_ids)
                ->pluck('user_id')
                ->all();

            /** Collect potential ticket ids for further draw */
            $potentialTicketIds = $raffle->tickets
                ->whereNotIn('user_id', $winnerUserIds)
                ->pluck('id')
                ->all();

            if(count($potentialTicketIds) === 0) {
                return;
            }

            $winnerTicketId = array_rand(array_flip($potentialTicketIds));
            $winner_ticket_ids[] = $winnerTicketId;

            $raffle->winner_ticket_ids = $winner_ticket_ids;
            $raffle->save();
        }
    }
}
