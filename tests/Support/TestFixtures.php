<?php

namespace Tests\Support;

class TestFixtures
{
    public static function credentials(): array
    {
        return [
            'admin' => ['email' => 'chef@saucebase.dev', 'password' => 'secretsauce'],
            'user' => ['email' => 'test@example.com', 'password' => 'secretsauce'],
            'subscriber' => ['email' => 'subscriber@example.com', 'password' => 'secretsauce'],
            'cancelled' => ['email' => 'cancelled@example.com', 'password' => 'secretsauce'],
        ];
    }
}
