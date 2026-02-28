<?php

namespace Modules\Auth\Exceptions;

use Exception;

/**
 * Exception used for authentication-related errors in the Auth module.
 */
class AuthException extends Exception
{
    /**
     * Create a new AuthException.
     *
     * This exception intentionally carries only a human-friendly message.
     * HTTP status handling is left to the caller (for example an exception
     * handler that maps exceptions to responses).
     *
     * @param  string  $message  Human-friendly error message.
     */
    public function __construct(string $message = 'Authentication error.')
    {
        parent::__construct($message);
    }

    /**
     * Factory for invalid credentials.
     *
     * Returns an exception with a localized message for failed authentication
     */
    public static function invalidCredentials(): self
    {
        return new self(trans('auth.failed'));
    }

    /**
     * Factory for throttling / too many attempts.
     *
     *
     * @param  int  $seconds  Number of seconds until the client may retry.
     */
    public static function throttle(int $seconds = 60): self
    {
        $message = trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);

        return new self($message);
    }
}
