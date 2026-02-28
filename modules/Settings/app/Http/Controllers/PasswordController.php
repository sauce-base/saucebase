<?php

namespace Modules\Settings\Http\Controllers;

use App\Helpers\Toast;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Http\Requests\UpdatePasswordRequest;

class PasswordController
{
    /**
     * Show the password change form.
     */
    public function edit(): Response
    {
        return Inertia::render('Settings::Profile/ChangePassword');
    }

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        $user->notify(new PasswordChangedNotification);

        Toast::success('Password changed successfully.');

        return redirect()->route('settings.profile');
    }
}
