<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Redirect the user to the Discord authentication page.
     *
     * @return RedirectResponse
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Obtain the user information from Discord.
     *
     * @return string
     */
    public function handleProviderCallback(): string
    {
        try {
            $discord = Socialite::driver('discord')->stateless()->user();

            $user = User::updateOrCreate(
            [
                'oauth_type' => 'discord',
                'oauth_id' => $discord->id
            ],
            [
                'name' => $discord->name,
                'email' => $discord->email ?? null,
                'avatar_url' => $discord->avatar,
                'oauth_id' => $discord->id,
                'oauth_type' => 'discord',
                'password' => encrypt($discord->id)
            ]);

            /** Authenticate the user */
            Auth::login($user);
        } catch (Exception $e) {
            Log::error($e);
        }

        $domain = parse_url(request()->root())['host'];

        if(str_starts_with($domain, 'admin')) {
            return redirect()->route('admin.home');
        }

        return redirect()->route('home');
    }
}
