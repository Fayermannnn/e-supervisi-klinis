<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Pembatas percobaan login gagal: maksimal 5 percobaan per 15 menit,
 * dikunci per kombinasi email + alamat IP.
 */
class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    public function key(string $email, string $ip): string
    {
        return Str::lower($email).'|'.$ip;
    }

    public function tooManyAttempts(string $key): bool
    {
        return RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS);
    }

    public function hit(string $key): void
    {
        RateLimiter::hit($key, self::DECAY_SECONDS);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function availableInSeconds(string $key): int
    {
        return RateLimiter::availableIn($key);
    }
}
