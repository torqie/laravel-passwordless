<?php

namespace Wiredrhino\LaravelPasswordless\Commands;

use Illuminate\Console\Command;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

class PurgePasswordlessTokensCommand extends Command
{
    public $signature = 'passwordless:purge
                        {--expired : Only remove expired tokens}
                        {--used : Only remove used (but not yet expired) tokens}';

    public $description = 'Purge expired and/or used passwordless tokens from the database';

    public function handle(): int
    {
        $onlyExpired = $this->option('expired');
        $onlyUsed    = $this->option('used');

        $query = PasswordlessToken::query();

        if ($onlyExpired && ! $onlyUsed) {
            $query->where('expires_at', '<', now());
        } elseif ($onlyUsed && ! $onlyExpired) {
            $query->whereNotNull('used_at');
        } else {
            // Default (no flags, or both flags): purge anything expired OR used
            $query->where(function ($q): void {
                $q->where('expires_at', '<', now())
                  ->orWhereNotNull('used_at');
            });
        }

        $count = $query->delete();

        $this->components->info("Purged {$count} passwordless token(s).");

        return self::SUCCESS;
    }
}

