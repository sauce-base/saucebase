<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function loginAs(string $email): array
    {
        $user = User::where('email', $email)->firstOrFail();

        // Web middleware (StartSession) doesn't run for playwright routes, so we
        // must start and save the session manually to persist it to the database.
        $session = app('session.store');
        $session->start();

        Auth::loginUsingId($user->id); // writes user ID + calls migrate() internally

        $session->save();

        $cookieName = config('session.cookie');
        $sessionId = $session->getId();

        // EncryptCookies middleware encrypts the session cookie on web routes.
        // Inject the same encrypted format so Laravel accepts the cookie.
        $encrypter = app('encrypter');
        $prefix = CookieValuePrefix::create($cookieName, $encrypter->getKey());
        $encryptedValue = $encrypter->encrypt($prefix.$sessionId, false);

        return [
            'name' => $cookieName,
            'value' => $encryptedValue,
            'domain' => parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost',
            'path' => '/',
        ];
    }
}
