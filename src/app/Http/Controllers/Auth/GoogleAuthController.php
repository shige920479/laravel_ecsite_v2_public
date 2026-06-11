<?php

namespace App\Http\Controllers\Auth;

use App\Events\GoogleAccountLinked;
use App\Events\GoogleLoginCompleted;
use App\Exceptions\GoogleAccountMismatchException;
use App\Exceptions\GoogleAuthenticationException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

        } catch (\Throwable $e) {
            throw new GoogleAuthenticationException();
        }

        $user = User::query()->where('google_id', $googleUser->id)->first();

        if (! $user) {
            $user = User::query()->where('email', $googleUser->email)->first();

            if ($user && $user->google_id !== null) {
                throw new GoogleAccountMismatchException();
            }

            if ($user) {
                $user->update(['google_id' => $googleUser->id]);

            } else {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::password()),
                    'email_verified_at' => now(),
                ]);
            }

            event(new GoogleAccountLinked($user));
        }

        Auth::login($user);
        event(new GoogleLoginCompleted($user));

        return to_route('home.index');
    }
}
