<?php

namespace Torqie\LaravelPasswordless\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordlessTokenIsValid
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->hasValidSignature()) {
            return redirect()
                ->to((string) config('passwordless.redirects.invalid_token', '/login'))
                ->withErrors(['token' => 'This magic link is invalid or has expired.']);
        }

        return $next($request);
    }
}

