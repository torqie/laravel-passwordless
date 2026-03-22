<?php

namespace Wiredrhino\LaravelPasswordless\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

interface AuthenticatesViaLoginCode
{
    /**
     * Authenticate the user identified by $email using the submitted $code.
     *
     * @throws ValidationException On invalid code or rate limit exceeded.
     */
    public function authenticate(string $email, string $code): RedirectResponse;
}
