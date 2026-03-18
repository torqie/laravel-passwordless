<?php

namespace Wiredrhino\LaravelPasswordless;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wiredrhino\LaravelPasswordless\Commands\LaravelPasswordlessCommand;

class LaravelPasswordlessServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-passwordless')
            ->hasConfigFile('passwordless')
            ->hasViews()
            ->hasMigration('create_passwordless_table')
            ->hasCommand(LaravelPasswordlessCommand::class);
    }
}
