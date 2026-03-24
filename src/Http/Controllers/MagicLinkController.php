<?php
namespace Torqie\LaravelPasswordless\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Response as InertiaResponse;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction;
use Torqie\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Torqie\LaravelPasswordless\Actions\ResolveUserForSendAction;
use Torqie\LaravelPasswordless\Events\MagicLinkSent;
use Torqie\LaravelPasswordless\Notifications\MagicLinkNotification;
class MagicLinkController extends Controller
{
    /**
     * GET /auth/magic-link
     */
    public function request(): View|InertiaResponse|RedirectResponse
    {
        if (Auth::guard((string) config('passwordless.guard', 'web'))->check()) {
            return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
        }
        if (config('passwordless.inertia')) {
            /** @var string $component */
            $component = config('passwordless.components.magic_link_request', 'Auth/MagicLinkRequest');
            return \Inertia\Inertia::render($component);
        }
        $view = config('passwordless.views.magic_link_request') ?? 'laravel-passwordless::magic-link.request';
        return view($view);
    }
    /**
     * POST /auth/magic-link
     */
    public function send(Request $request, GenerateMagicLinkAction $action, ResolveUserForSendAction $resolver): View|InertiaResponse|RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = $resolver->handle((string) $request->input('email'), (string) $request->ip());
        // Always show the "sent" view to avoid user enumeration
        if ($user !== null) {
            $url = $action->generate($user);
            // @phpstan-ignore method.notFound ($user is expected to use the Notifiable trait)
            $user->notify(new MagicLinkNotification($url));
            event(new MagicLinkSent($user, $url));
        }
        if (config('passwordless.inertia')) {
            /** @var string $component */
            $component = config('passwordless.components.magic_link_sent', 'Auth/MagicLinkSent');
            return \Inertia\Inertia::render($component);
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