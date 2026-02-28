<?php

namespace Modules\Settings\Http\Controllers;

use App\Helpers\Toast;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Http\Requests\UpdateProfileAvatarRequest;
use Modules\Settings\Http\Requests\UpdateProfileInfoRequest;

class ProfileController
{
    /**
     * Show the read-only profile view.
     */
    public function show(): Response
    {
        $user = auth()->user();

        return Inertia::render('Settings::Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'last_login_at' => $user->last_login_at,
                'social_accounts' => $user->connected_providers,
            ],
            'available_providers' => config('services.socialite_providers', []),
        ]);
    }

    /**
     * Show the profile edit form.
     */
    public function edit(): Response
    {
        $user = auth()->user();

        return Inertia::render('Settings::Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'has_uploaded_avatar' => $user->hasMedia('avatars'),
                'has_password' => ! empty($user->password),
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function updateInfo(UpdateProfileInfoRequest $request): RedirectResponse
    {
        auth()->user()->update($request->validated());

        Toast::success('Profile updated successfully!');

        return back();
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(UpdateProfileAvatarRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatars');
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');
        }

        Toast::success('Avatar updated successfully.');

        return back();
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(): RedirectResponse
    {
        auth()->user()->clearMediaCollection('avatars');

        Toast::success('Avatar removed successfully.');

        return back();
    }
}
