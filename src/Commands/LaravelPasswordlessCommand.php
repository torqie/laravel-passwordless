<?php

namespace Wiredrhino\LaravelPasswordless\Commands;

use Illuminate\Console\Command;

class LaravelPasswordlessCommand extends Command
{
    public $signature = 'laravel-passwordless';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
