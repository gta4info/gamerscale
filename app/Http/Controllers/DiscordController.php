<?php

namespace App\Http\Controllers;

use App\Enums\UserDiscordConfirmedEnum;
use App\Models\User;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     *
     * @param string|null $token
     * @return RedirectResponse
     */
    public function redirectToProvider(string $token = null): RedirectResponse
    {
        /** Keep token to use in @handleProviderCallback */
        if(!is_null($token)) {
            return Socialite::driver('discord')
                ->with(['state' => 'token='.$token])
                ->redirect();
        }

        return Socialite::driver('discord')->redirect();
    }

    /**
     * Obtain the user information from Discord.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function handleProviderCallback(Request $request): RedirectResponse
    {
        try {
            $discord = Socialite::driver('discord')->stateless()->user();
            $user = User::where('oauth_id', $discord->id)->first();

            /** Get and parse Discord's state from URL if isset */
            $token = $request->input('state');
            parse_str($token, $jwt);

            /** Create new user */
            if (!$user) {
                $user = User::create([
                    'name' => $discord->name,
                    'email' => $discord->email ?? $discord->id . '@' . 'gmail.com',
                    'oauth_id' => $discord->id,
                    'oauth_type' => 'discord',
                    'password' => encrypt($discord->id)
                ]);
            }

            /** Authenticate the user */
            Auth::login($user);

            /** Checks if user is not yet passed the confirmation through Discord bot */
            if($user->is_confirmed_from_discord === UserDiscordConfirmedEnum::TYPE_NOT_CONFIRMED) {
                /** Try to update is_confirmed_from_discord state for user */
                $this->updateUserIsConfirmedFromDiscord($jwt, $user);
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        return redirect('/');
    }

    /**
     * @param array $data
     * @return bool
     */
    protected static function hasDiscordTokenInRequest(array $data): bool
    {
        return !empty($data) && isset($data['token']);
    }

    /**
     * @param string $token
     * @return Collection|View
     */
    protected static function decodeJwtToken(string $token): Collection|View
    {
        try {
            $decodedToken = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));

            return collect($decodedToken);
        } catch (ExpiredException $e) {
            /** Redirect user to error page with message */
            return view('discord-confirmation-error')
                ->with(['message' => 'Токен не валиден. Повторите 2 этап регистрации, нажав на кнопку "Регистрация на турнир"']);
        }
    }

    /**
     * @param string $token
     * @return string
     */
    protected static function generateJwtToken(string $token): string
    {
        $token = self::decodeJwtToken($token);

        $payload = [
            'token' => $token->get('token'),
            'user' => $token->get('user'),
            'iat' => $token->get('iat'),
            'is_confirmed' => true // Tells bot that user's Discord id is actually matches with requested
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function sendJwtToken(string $token): bool
    {
        try {
            $client = new Client();
            $client->post(config('app.discord_bot_url'), [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'token' => self::generateJwtToken($token)
                ]
            ]);

            return true;
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }

    /**
     * @param array $jwt
     * @param User $user
     * @return void
     */
    protected function updateUserIsConfirmedFromDiscord(array $jwt, User $user): void
    {
        /** Check for token in URL params */
        if(!self::hasDiscordTokenInRequest($jwt)) {
            return;
        }
        /** Check for matching Discord ID's for user in token with stored */
        if(self::decodeJwtToken($jwt['token'])->get('user') != $user->oauth_id) {
            return;
        }
        /** Send new token to Discord Bot */
        if(!$this->sendJwtToken($jwt['token'])) {
            return;
        }

        /** Mark user's discord as confirmed */
        $user->update([
            'is_confirmed_from_discord' => UserDiscordConfirmedEnum::TYPE_CONFIRMED->value
        ]);
    }
}
