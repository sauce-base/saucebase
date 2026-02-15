<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    /** @return User&Authenticatable */
    protected function createUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::USER);

        return $user;
    }
}
