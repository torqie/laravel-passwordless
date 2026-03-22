<?php

namespace Torqie\LaravelPasswordless\Contracts;

use Illuminate\Http\RedirectResponse;

interface AuthenticatesViaLoginCode
{
    /**
     * Authenticate the user identified by $email using the submitted $code.
     *
     * @throws \Illuminate\Validation\ValidationException  On invalid code or rate limit exceeded.
     */
    public function authenticate(string $email, string $code): RedirectResponse;
}

