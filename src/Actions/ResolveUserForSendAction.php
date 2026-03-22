<?php

namespace Torqie\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ResolveUserForSendAction
{
    /**
     * Throttle the send endpoint and look up the authenticatable user.
     *
     * Returns null when no account matches the email — the caller must handle
     * this silently to prevent user-enumeration.
     */
    public function handle(string $email, string $ip): ?Authenticatable
    {
        $rateLimitKey = 'passwordless:send:'.$ip.'|'.$email;
        $sendLimit = (int) config('passwordless.rate_limits.send', 5);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $sendLimit)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw ValidationException::withMessages([
                'email' => ["Too many requests. Please try again in {$seconds} seconds."],
            ]);
        }

        RateLimiter::hit($rateLimitKey, 60);

        /** @var class-string $userModel */
        $userModel = config('passwordless.user_model');

        return $userModel::where('email', $email)->first();
    }
}
