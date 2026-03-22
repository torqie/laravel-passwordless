<?php

namespace Wiredrhino\LaravelPasswordless\Contracts;

use Illuminate\Http\RedirectResponse;

interface AuthenticatesViaMagicLink
{
    /**
     * Authenticate the user using the given plain-text token.
     */
    public function authenticate(string $token): RedirectResponse;
}
