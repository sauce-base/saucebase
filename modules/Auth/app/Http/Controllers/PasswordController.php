<?php

namespace Modules\Auth\Http\Controllers;

use App\Helpers\Toast;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user->notify(new PasswordChangedNotification);

        Toast::success('Password changed successfully.');

        return back();
    }
}
