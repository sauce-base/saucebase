<?php

namespace Modules\Auth\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Exceptions\SocialiteException;
use Modules\Auth\Models\SocialAccount;
use Modules\Auth\Services\SocialiteService;

/**
 * @phpstan-ignore trait.unused
 */
trait Sociable
{
    /**
     * Get the social accounts associated with the user.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get connected providers for profile display
     */
    public function getConnectedProvidersAttribute(): array
    {
        return $this->socialAccounts()
            ->orderBy('last_login_at', 'desc')
            ->get(['provider', 'last_login_at', 'provider_avatar_url'])
            ->toArray();
    }

    /**
     * Disconnect a social account from the user.
     *
     * @throws SocialiteException
     */
    // Disconnect logic has been moved to the `SocialiteService`.

    /**
     * Delegate disconnect to the SocialiteService.
     *
     * @throws SocialiteException
     */
    public function disconnectSocialProvider(string $provider): void
    {
        app(SocialiteService::class)->disconnectProvider($this, $provider);
    }

    /**
     * Get the latest provider avatar URL
     */
    public function getLatestProviderAvatarUrlAttribute(): ?string
    {
        /** @var SocialAccount|null $latestAccount */
        $latestAccount = $this->socialAccounts()
            ->whereNotNull('provider_avatar_url')
            ->orderBy('last_login_at', 'desc')
            ->first();

        return $latestAccount ? $latestAccount->provider_avatar_url : null;
    }
}
