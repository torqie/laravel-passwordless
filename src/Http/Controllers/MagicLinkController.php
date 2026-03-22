<?php

namespace Wiredrhino\LaravelPasswordless\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction;
use Wiredrhino\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Wiredrhino\LaravelPasswordless\Events\MagicLinkSent;
use Wiredrhino\LaravelPasswordless\Notifications\MagicLinkNotification;

class MagicLinkController extends Controller
{
    /**
     * GET /auth/magic-link
     */
    public function request(): View
    {
        $view = config('passwordless.views.magic_link_request') ?? 'laravel-passwordless::magic-link.request';

        return view($view);
    }

    /**
     * POST /auth/magic-link
     */
    public function send(Request $request, GenerateMagicLinkAction $action): View|RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        /** @var class-string $userModel */
        $userModel = config('passwordless.user_model');
        $user      = $userModel::where('email', $request->input('email'))->first();

        // Always show the "sent" view to avoid user enumeration
        if ($user !== null) {
            $url = $action->generate($user);
            $user->notify(new MagicLinkNotification($url));
            event(new MagicLinkSent($user, $url));
        }

        $view = config('passwordless.views.magic_link_sent') ?? 'laravel-passwordless::magic-link.sent';

        return view($view);
    }

    /**
     * GET /auth/magic-link/{token}
     * Signature validation is handled by the `passwordless.signed` middleware.
     */
    public function authenticate(
        Request $request,
        string $token,
        AuthenticateViaMagicLinkAction $action
    ): RedirectResponse {
        return $action->authenticate($token);
    }
}
