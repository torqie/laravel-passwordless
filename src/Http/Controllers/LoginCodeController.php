<?php

namespace Wiredrhino\LaravelPasswordless\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction;
use Wiredrhino\LaravelPasswordless\Actions\GenerateLoginCodeAction;
use Wiredrhino\LaravelPasswordless\Notifications\LoginCodeNotification;

class LoginCodeController extends Controller
{
    /**
     * GET /auth/code
     * Show the code request form.
     */
    public function request(): View
    {
        $view = config('passwordless.views.login_code_request') ?? 'laravel-passwordless::login-code.request';

        return view($view);
    }

    /**
     * POST /auth/code
     * Send the one-time code to the provided email address.
     */
    public function send(Request $request, GenerateLoginCodeAction $action): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /** @var class-string $userModel */
        $userModel = config('passwordless.user_model');
        $user      = $userModel::where('email', $request->input('email'))->first();

        // Always proceed to the verify screen to prevent user enumeration
        if ($user !== null) {
            $code = $action->generate($user);
            $user->notify(new LoginCodeNotification($code));
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

