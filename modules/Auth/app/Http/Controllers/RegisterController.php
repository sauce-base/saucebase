<?php

namespace Modules\Auth\Http\Controllers;

use App\Helpers\Toast;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Auth\Http\Requests\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth::Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->only(['name', 'email', 'password']);
        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        Toast::default(
            __('auth.welcome', ['name' => $user->name]),
        );

        return redirect()->intended(route('dashboard'));
    }
}
