<?php

namespace Torqie\LaravelPasswordless\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Torqie\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction;
use Torqie\LaravelPasswordless\Actions\GenerateLoginCodeAction;
use Torqie\LaravelPasswordless\Actions\ResolveUserForSendAction;
use Torqie\LaravelPasswordless\Events\LoginCodeSent;
use Torqie\LaravelPasswordless\Notifications\LoginCodeNotification;

class LoginCodeController extends Controller
{
    /**
     * GET /auth/code
     * Show the code request form.
     */
    public function request(): View|RedirectResponse
    {
        if (Auth::guard((string) config('passwordless.guard', 'web'))->check()) {
            return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
        }

        $view = config('passwordless.views.login_code_request') ?? 'laravel-passwordless::login-code.request';

        return view($view);
    }

    /**
     * POST /auth/code
     * Send the one-time code to the provided email address.
     */
    public function send(Request $request, GenerateLoginCodeAction $action, ResolveUserForSendAction $resolver): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $resolver->handle((string) $request->input('email'), (string) $request->ip());

        // Always proceed to the verify screen to prevent user enumeration
        if ($user !== null) {
            $code = $action->generate($user);
            // @phpstan-ignore method.notFound ($user is expected to use the Notifiable trait)
            $user->notify(new LoginCodeNotification($code));
            event(new LoginCodeSent($user));
        }

        // Store the email in session so the verify form knows whose code to check
        $request->session()->put('passwordless.pending_email', $request->input('email'));

        return redirect()->route('passwordless.login-code.verify');
    }

    /**
     * GET /auth/code/verify
     * Show the code entry form.
     */
    public function verify(Request $request): View|RedirectResponse
    {
        if (Auth::guard((string) config('passwordless.guard', 'web'))->check()) {
            return redirect()->to((string) config('passwordless.redirects.after_login', '/dashboard'));
        }

        $email = $request->session()->get('passwordless.pending_email');

        if ($email === null) {
            return redirect()->route('passwordless.login-code.request')
                ->withErrors(['email' => 'Please request a new code.']);
        }

        $view = config('passwordless.views.login_code_verify') ?? 'laravel-passwordless::login-code.verify';

        return view($view, ['email' => $email]);
    }

    /**
     * POST /auth/code/verify
     * Verify the submitted code and authenticate the user.
     */
    public function authenticate(Request $request, AuthenticateViaLoginCodeAction $action): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $email = $request->session()->get('passwordless.pending_email');

        if ($email === null) {
            return redirect()->route('passwordless.login-code.request')
                ->withErrors(['email' => 'Session expired. Please request a new code.']);
        }

        $response = $action->authenticate($email, (string) $request->input('code'));

        // Clear the pending email from session on success
        $request->session()->forget('passwordless.pending_email');

        return $response;
    }
}
