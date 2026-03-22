<?php

namespace Wiredrhino\LaravelPasswordless;

use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaLoginCodeAction;
use Wiredrhino\LaravelPasswordless\Actions\AuthenticateViaMagicLinkAction;
use Wiredrhino\LaravelPasswordless\Actions\GenerateLoginCodeAction;
use Wiredrhino\LaravelPasswordless\Actions\GenerateMagicLinkAction;
use Wiredrhino\LaravelPasswordless\Commands\PurgePasswordlessTokensCommand;
use Wiredrhino\LaravelPasswordless\Contracts\AuthenticatesViaLoginCode;
use Wiredrhino\LaravelPasswordless\Contracts\AuthenticatesViaMagicLink;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesLoginCode;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesMagicLink;
use Wiredrhino\LaravelPasswordless\Http\Middleware\EnsurePasswordlessTokenIsValid;

class LaravelPasswordlessServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-passwordless')
            ->hasConfigFile('passwordless')
            ->hasViews('laravel-passwordless')
            ->hasRoutes('passwordless')
            ->hasMigrations([
                'create_passwordless_table',
                'make_password_nullable_on_users_table',
            ])
            ->hasCommand(PurgePasswordlessTokensCommand::class);
    }

    public function registeringPackage(): void
    {
        // Bind contracts → configurable action implementations (resolved lazily)
        $this->app->bind(GeneratesMagicLink::class, function ($app) {
            /** @var class-string<GeneratesMagicLink> $class */
            $class = config('passwordless.actions.generate_magic_link', GenerateMagicLinkAction::class);

            return $app->make($class);
        });

        $this->app->bind(AuthenticatesViaMagicLink::class, function ($app) {
            /** @var class-string<AuthenticatesViaMagicLink> $class */
            $class = config('passwordless.actions.authenticate_magic_link', AuthenticateViaMagicLinkAction::class);

            return $app->make($class);
        });

        $this->app->bind(GeneratesLoginCode::class, function ($app) {
            /** @var class-string<GeneratesLoginCode> $class */
            $class = config('passwordless.actions.generate_login_code', GenerateLoginCodeAction::class);

            return $app->make($class);
        });

        $this->app->bind(AuthenticatesViaLoginCode::class, function ($app) {
            /** @var class-string<AuthenticatesViaLoginCode> $class */
            $class = config('passwordless.actions.authenticate_login_code', AuthenticateViaLoginCodeAction::class);

            return $app->make($class);
        });

        // Singleton for the facade target (cloned per-user via for())
        $this->app->singleton(LaravelPasswordless::class);
    }

    public function bootingPackage(): void
    {
        $this->app->make(Router::class)
            ->aliasMiddleware('passwordless.signed', EnsurePasswordlessTokenIsValid::class);
    }
}
