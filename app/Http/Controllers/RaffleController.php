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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Symfony\Component\HttpFoundation\Response;

class RaffleController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:4|max:255',
            'currency_type' => [new Enum(RaffleCurrencyTypeEnum::class)],
            'description' => 'required|min:4',
            'cost' => 'numeric|min:0',
            'winners_amount' => 'integer|min:1',
            'start_at' => 'required|date_format:Y-m-d H:i',
            'end_at' => 'required|date_format:Y-m-d H:i|after:start_at',
            'discord_message_content' => 'nullable|max:255'
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
            'currency_type' => [new Enum(RaffleCurrencyTypeEnum::class)],
            'description' => 'min:4',
            'cost' => 'numeric|min:0',
            'winners_amount' => 'integer|min:1',
            'start_at' => 'date_format:Y-m-d H:i',
            'end_at' => 'date_format:Y-m-d H:i|after:start_at',
            'discord_message_content' => 'max:255'
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

    public function get(Raffle $raffle): JsonResponse
    {
        return response()->json($raffle);
    }

    public function publish(Raffle $raffle): JsonResponse
    {
        try {
            $raffle->update(['is_published' => true]);
        } catch (\Exception $e) {
            Log::info('Error on publishing raffle id: ' . $raffle->id);
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'raffle' => $raffle,
            'message' => 'Raffle published.'
        ]);
    }

    public function delete(Raffle $raffle): JsonResponse
    {
        try {
            if($raffle->tickets()->count()) {
                $deleteTickets = $this->deleteTickets($raffle);

                if(!$deleteTickets) throw new \Exception('Произошла ошибка при удалении розыгрыша');
            }

            $raffle->delete();

        } catch (\Exception $e) {
            Log::info('Error on deleting raffle id: ' . $raffle->id);
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Raffle deleted.']);
    }

    public function setDiscordMessageId(Request $request, Raffle $raffle): JsonResponse
    {
        try {
            $raffle->update(['discord_message_id' => $request->post('discord_message_id')]);
        } catch (\Exception $e) {
            Log::info('Error on updating discord message id on raffle id: ' . $raffle->id);
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Raffle\'s discord message id updated.']);
    }

    public function participate(Request $request, Raffle $raffle): JsonResponse
    {
        if($raffle->status === RaffleStatusEnum::COMPLETED->value) {
            throw new HttpResponseException(
                response()->json(['message' => 'Розыгрыш завершен.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        if($raffle->status === RaffleStatusEnum::PENDING->value) {
            throw new HttpResponseException(
                response()->json(['message' => 'Розыгрыш еще не начался.'], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        $ticketsAmount = $request->post('tickets_amount', 1);
        $user = (new UserController())->updateOrCreate($request);

        try {
            /** Purchasing tickets if raffle is not free */
            if($raffle->currency_type !== RaffleCurrencyTypeEnum::FREE->value) {
                $this->purchaseTickets($raffle, $user, $ticketsAmount);
            } else {
                if($raffle->tickets()->where('user_id', '=', $user->id)->count() >= 1) {
                    throw new \Exception('Вы уже участвуете в данном розыгрыше. В бесплатных розыгрышах у вас может быть только **1 билет**!');
                }

                $ticketsAmount = 1;
            }

            for($i = 1; $i <= $ticketsAmount; $i++) {
                $raffle->tickets()->create([
                    'user_id' => $user->id
                ]);
            }

            // Count unique users in raffle and update amount
            $participants = count(array_unique($raffle->tickets()->pluck('user_id')->toArray()));
            $raffle->update([
                'participants_amount' => $participants
            ]);
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

    public function deleteTickets(Raffle $raffle): bool
    {
        // If raffle is free
        if($raffle->currency_type === RaffleCurrencyTypeEnum::FREE->value) {
            $raffle->tickets()->delete();

            return true;
        }

        // For paid raffles return balance to user
        $userIds = array_unique($raffle->tickets()->pluck('user_id')->toArray());

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $res = $this->unParticipate($raffle, $user);

            if(!$res) return false;
        }

        return true;
    }

    public function unParticipate(Raffle $raffle, User $user): bool
    {
        try {
            $totalTicketsAmount = $raffle->tickets()->where('user_id', '=', $user->id)
                ->count();

            if($raffle->currency_type === RaffleCurrencyTypeEnum::VBUCKS->value) {
                $balanceType = UserBalanceTypeEnum::VBUCKS->value;
            } else {
                $balanceType = UserBalanceTypeEnum::FIAT->value;
            }

            $totalAmount = $raffle->cost * $totalTicketsAmount;
            $userCurrentBalance = (new UserBalanceController())->getCurrentBalanceByType($user, $balanceType);

            $user->balance()->create([
                'amount' => $userCurrentBalance + $totalAmount,
                'type' => $balanceType,
                'comment' => "Начислено за возврат {$totalTicketsAmount} билетов в розыгрыше id={$raffle->id}"
            ]);

            $raffle->tickets()->where('user_id', '=', $user->id)->delete();
        } catch (\Exception $exception) {
            Log::info('Error on deleting tickets for user id: ' . $user->id);
            Log::error($exception->getMessage());

            return false;
        }

        return true;
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
            throw new \Exception('Недостаточно средств для покупки билетов. На вашем счету: ' . $userBalance);
        }

        if($raffle->currency_type === RaffleCurrencyTypeEnum::VBUCKS->value) {
            $balanceType = UserBalanceTypeEnum::VBUCKS->value;
        } else {
            $balanceType = UserBalanceTypeEnum::FIAT->value;
        }

        $user->balance()->create([
            'amount' => $userBalance - $amountToSpend,
            'type' => $balanceType,
            'comment' => "Списано за покупку {$ticketsAmount} билетов в розыгрыше id={$raffle->id}"
        ]);
    }

    public function getUserSummaries(Request $request, Raffle $raffle): JsonResponse
    {
        $user = (new UserController())->updateOrCreate($request);
        $totalTicketToUser = $raffle->tickets()
            ->where('user_id', '=', $user->id)
            ->count();

        return response()->json([
            'raffle_title' => $raffle->title,
            'tickets' => $totalTicketToUser,
            'winning_chance' => $totalTicketToUser / $raffle->tickets()->count() * 100
        ]);
    }

    public function getUserSummariesList(Request $request): JsonResponse
    {
        $user = (new UserController())->updateOrCreate($request);
        $data = [];
        $raffles = (new UserController())->activeRaffles($user);

        foreach ($raffles as $raffle) {
            $totalTicketToUser = $raffle->tickets()
                ->where('user_id', '=', $user->id)
                ->count();

            $data[] = [
                'title' => $raffle->title,
                'tickets' => $totalTicketToUser,
                'chance' => $totalTicketToUser / $raffle->tickets()->count() * 100
            ];
        }

        return response()->json($data);
    }

    public function draw(Raffle $raffle): void
    {
        if(!$raffle->is_published) return;

        for($i = 1; $i <= $raffle->winners_amount; $i++) {
            $winner_ticket_ids = $raffle->winner_ticket_ids ?? [];
            if(count($winner_ticket_ids) >= $raffle->winners_amount) {
                break;
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
                break;
            }

            $winnerTicketId = array_rand(array_flip($potentialTicketIds));
            $winner_ticket_ids[] = $winnerTicketId;

            $raffle->winner_ticket_ids = $winner_ticket_ids;
            $raffle->save();
        }

        $this->afterDrawDiscordNotification($raffle);
    }

    public function afterDrawDiscordNotification(Raffle $raffle): void
    {
        $winners = [];

        $raffle->tickets()
            ->with('user')
            ->whereIn('id', $raffle->winner_ticket_ids)
            ->get()
            ->each(function ($ticket) use (&$winners) {
                $winners[] = $ticket->user->oauth_id;
            });

        $this->sendChangedInfoToDiscordBot(['winners' => $winners], $raffle->discord_message_id);
    }

    public function notPublishedList(): JsonResponse
    {
        $list = Raffle::where('is_published', '=', 0)
            ->select(['id', 'title'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['list' => $list]);
    }

    public function notCompletedList(): JsonResponse
    {
        $list = Raffle::where('status', '!=', RaffleStatusEnum::COMPLETED->value)
            ->select(['id', 'title'])
            ->orderByDesc('id')
            ->get();

        return response()->json(['list' => $list]);
    }

    public function sendChangedInfoToDiscordBot(array $changes, string $messageId): bool
    {
        if(!count($changes)) return false;

        $uri = config('app.DISCORD_BOT_APP_URL')  . '/update-message';
        $data = ['discord_message_id' => $messageId];

        foreach ($changes as $key => $val) {
            $data[$key] = $val;
        }

        try {
            $res = Http::post($uri, $data);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
            return false;
        }

        return $res->status() === 200;
    }

    public function getParticipationInfo(Raffle $raffle): JsonResponse
    {
        return response()->json([
            'id' => $raffle->id,
            'type' => $raffle->currency_type,
            'cost' => $raffle->cost
        ]);
    }
}
