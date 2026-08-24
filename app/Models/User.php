<?php

namespace App\Models;

use App\Enums\Role;
use App\Settings\LocalizationSettings;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements FilamentUser, HasLocalePreference, HasMedia
    // , MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Set the user's email address.
     */
    protected function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Register media collections for this model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Get avatar with fallback to default
     */
    public function getAvatarAttribute(): string
    {
        // First priority: Media library uploaded avatar
        $mediaAvatar = $this->getFirstMediaUrl('avatars');
        if ($mediaAvatar) {
            return $mediaAvatar;
        }

        // Second priority: Social login avatar URL from database
        if (! empty($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }

        // Final fallback: Default avatar
        return asset('images/default-avatar.jpg');
    }

    /**
     * The language to address this user in.
     *
     * Laravel reads this contract itself when sending mail and notifications, including
     * queued ones, which is what makes a user's choice outlive the request they made it in.
     *
     * Null for a language the admin has since turned off, rather than a substitute one:
     * `withLocale()` then leaves the ambient locale alone, which the middleware has already
     * resolved against the enabled set.
     */
    public function preferredLocale(): ?string
    {
        if ($this->locale === null) {
            return null;
        }

        $enabled = app(LocalizationSettings::class)->enabled();

        return array_key_exists($this->locale, $enabled) ? $this->locale : null;
    }

    /**
     * Check if user is an administrator
     *
     * @return bool True if the user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Check if user is a regular user
     *
     * @return bool True if the user has user role
     */
    public function isUser(): bool
    {
        return $this->hasRole(Role::USER);
    }

    /**
     * Check if user is a subscriber
     *
     * @return bool True if the user has subscriber role
     */
    public function isSubscriber(): bool
    {
        return $this->hasRole(Role::SUBSCRIBER);
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
