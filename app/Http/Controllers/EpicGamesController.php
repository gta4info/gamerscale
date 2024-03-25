<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class EpicGamesController extends Controller
{
    /**
     * Redirect the user to the Epic Games authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('epicgames')->redirect();
    }

    /**
     * Obtain the user information from Epic Games.
     *
     * @return string
     */
    public function handleProviderCallback(): RedirectResponse
    {
        $epicgames = Socialite::driver('epicgames')->stateless()->user();

        try {
            auth()->user()->update([
                'epic_id' => $epicgames->user['sub'],
                'epic_name' => $epicgames->user['preferred_username'],
            ]);
        } catch (Exception $exception) {
            Log::error('Ошибка при привязке Epic Games к юзеру id: ' . auth()->id());
            Log::error($exception->getMessage());
            Log::error(json_encode($epicgames));
        }

        return redirect('/profile');
    }
}
