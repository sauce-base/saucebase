<?php

namespace Modules\Auth\Http\Controllers;

use App\Helpers\Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Modules\Auth\Exceptions\SocialiteException;
use Modules\Auth\Services\SocialiteService;
use Symfony\Component\HttpFoundation\Response as RedirectResponse;

class SocialiteController extends Controller
{
    private SocialiteService $socialiteService;

    public function __construct(SocialiteService $socialiteService)
    {
        $this->socialiteService = $socialiteService;
    }

    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $validator = Validator::make(['provider' => $provider], [
            'provider' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->with('error', trans('socialite.error'));
        }

        // Check if user is already authenticated (account linking flow)
        if (Auth::check()) {
            try {
                /** @var User $socialUser */
                $socialUser = Socialite::driver($provider)->user();
                $this->socialiteService->linkAccountToUser(Auth::user(), $provider, $socialUser);
                Toast::success(trans('socialite.account_connected', ['provider' => ucfirst($provider)]));
            } catch (SocialiteException $e) {
                Toast::error($e->getMessage());
            } catch (\Exception $e) {
                Toast::error(trans('socialite.error'));
                report($e);
            } finally {
                return redirect()->route('settings.profile');
            }
        }

        // Guest user - login/registration flow
        $user = $this->socialiteService->handleCallback($provider);

        Auth::login($user);

        request()->session()->regenerate();

        Toast::default(
            __($user->wasRecentlyCreated ? 'auth.welcome' : 'auth.welcome-back', [
                'name' => $user->name,
            ]),
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Disconnect a social provider from user account
     */
    public function disconnect(string $provider): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->socialiteService->disconnectProvider($user, $provider);

            Toast::success(trans('socialite.account_disconnected', ['provider' => $provider]));
        } catch (SocialiteException $e) {
            Toast::error($e->getMessage());
        }

        return back();
    }
}
