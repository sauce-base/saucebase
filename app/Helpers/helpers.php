<?php

function is_demo_mode(): bool
{
    if (! config('app.demo_mode')) {
        return false;
    }

    $bypassEmail = config('app.demo_mode_bypass_email');

    if (! $bypassEmail) {
        return true;
    }

    return auth()->user()?->email !== $bypassEmail;
}

function anonymize_email(string $email): string
{
    if (! str_contains($email, '@')) {
        return $email;
    }

    [$local, $domain] = explode('@', $email, 2);

    return substr($local, 0, 1).str_repeat('*', max(3, strlen($local) - 1)).'@'.$domain;
}
