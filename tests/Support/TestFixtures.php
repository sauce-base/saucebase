<?php

namespace Tests\Support;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestFixtures
{
    /**
     * Password for the shared, fixed-email accounts.
     *
     * Fixed rather than random because these rows are created once and then only
     * ever read. See seedSharedAccounts().
     */
    public const SHARED_PASSWORD = 'e2e-shared-password';

    /**
     * Accounts referenced by fixed email across specs (billing, mainly).
     *
     * @var array<string, string>
     */
    private const SHARED_ACCOUNTS = [
        'subscriber@example.com' => 'Subscriber User',
        'cancelled@example.com' => 'Cancelled User',
    ];

    /**
     * Create the shared, fixed-email accounts.
     *
     * Called once from the Playwright `database.setup` project, which runs on its
     * own before any worker starts. Doing it here — rather than per test — is what
     * makes parallel workers safe: `syncRoles()` is a detach-then-attach, so two
     * workers running it against the same user race into a duplicate primary key
     * on `model_has_roles`, and a per-test random password meant one worker could
     * overwrite the credentials another worker was still logging in with.
     */
    public static function seedSharedAccounts(): void
    {
        foreach (self::SHARED_ACCOUNTS as $email => $name) {
            $account = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(self::SHARED_PASSWORD),
                    'email_verified_at' => now(),
                ]
            );

            $account->syncRoles([Role::USER->value]);
        }
    }

    /**
     * Credentials for a single test.
     *
     * The admin and user accounts are created fresh per call, so each test — and
     * so each worker — gets its own rows with factory-unique emails. The shared
     * accounts are only read; they are provisioned by seedSharedAccounts().
     *
     * @return array<string, array{email: string, password: string}>
     */
    public static function credentials(): array
    {
        $password = Str::password(12, symbols: false);

        $admin = User::factory()->create([
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles([Role::ADMIN->value]);

        $user = User::factory()->create([
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([Role::USER->value]);

        return [
            'admin' => ['email' => $admin->email, 'password' => $password],
            'user' => ['email' => $user->email, 'password' => $password],
            'subscriber' => ['email' => 'subscriber@example.com', 'password' => self::SHARED_PASSWORD],
            'cancelled' => ['email' => 'cancelled@example.com', 'password' => self::SHARED_PASSWORD],
        ];
    }
}
