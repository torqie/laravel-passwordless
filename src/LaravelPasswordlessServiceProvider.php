<?php

namespace Wiredrhino\LaravelPasswordless;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wiredrhino\LaravelPasswordless\Commands\LaravelPasswordlessCommand;

class LaravelPasswordlessServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-passwordless')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_passwordless_table')
            ->hasCommand(LaravelPasswordlessCommand::class);
    }
}
