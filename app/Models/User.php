<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Modules\Auth\Traits\useSocialite;

class User extends Authenticatable implements HasMedia
{
    use HasFactory,
        HasRoles,
        InteractsWithMedia,
        Notifiable;

    // use MustVerifyEmail;
    // use useSocialite;

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
}
