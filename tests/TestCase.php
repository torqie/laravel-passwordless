<?php

namespace Torqie\LaravelPasswordless\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Torqie\LaravelPasswordless\LaravelPasswordlessServiceProvider;
use Torqie\LaravelPasswordless\Tests\Models\User;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpWebMiddleware();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelPasswordlessServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // Database — fresh in-memory SQLite for every test
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Session & cache must use array driver for isolation
        config()->set('session.driver', 'array');
        config()->set('cache.default', 'array');

        // A fixed key so URL::temporarySignedRoute works within the same test
        config()->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        config()->set('app.url', 'http://localhost');

        // Bind the test User model into the package
        config()->set('passwordless.user_model', User::class);
        config()->set('passwordless.redirects.after_login', '/dashboard');
        config()->set('passwordless.redirects.invalid_token', '/login');

        // Auth wired to the test User model
        config()->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model'  => User::class,
        ]);
        config()->set('auth.guards.web', [
            'driver'   => 'session',
            'provider' => 'users',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function setUpDatabase(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('Test User');
            $table->string('email')->unique();
            $table->timestamps();
        });

        /** @var \Illuminate\Database\Migrations\Migration $migration */
        $migration = include __DIR__ . '/../database/migrations/create_passwordless_table.php.stub';
        $migration->up();
    }

    private function setUpWebMiddleware(): void
    {
        // Remove CSRF from the web group — keeps session middleware intact
        $this->app->make(Router::class)->middlewareGroup('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Register the package's view namespace explicitly so HTTP tests can render views
        $this->app->make('view')->addNamespace(
            'laravel-passwordless',
            realpath(__DIR__ . '/../resources/views')
        );
    }
}
