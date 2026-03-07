<?php

function anonymize_email(string $email): string
{
    if (! str_contains($email, '@')) {
        return $email;
    }

    [$local, $domain] = explode('@', $email, 2);

    return substr($local, 0, 1).str_repeat('*', max(3, strlen($local) - 1)).'@'.$domain;
}
