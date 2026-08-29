<?php

namespace Torqie\LaravelPasswordless\Commands;

use Illuminate\Console\Command;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;

class PurgePasswordlessTokensCommand extends Command
{
    public $signature = 'passwordless:purge
                        {--expired : Only remove expired tokens}
                        {--used : Only remove used (but not yet expired) tokens}
                        {--days= : Only remove tokens created at least this many days ago}';

    public $description = 'Purge expired and/or used passwordless tokens from the database';

    /**
     * Retention is worth scheduling, not just running by hand. Every retained
     * login-code row is another chance for a freshly generated code to collide
     * on the unique index, so in an app that issues codes at any volume this
     * belongs in the scheduler:
     *
     *     Schedule::command('passwordless:purge')->daily();
     */
    public function handle(): int
    {
        $onlyExpired = $this->option('expired');
        $onlyUsed = $this->option('used');

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

        if ($days = $this->option('days')) {
            $query->where('created_at', '<=', now()->subDays((int) $days));
        }

        $count = $query->delete();

        $this->components->info("Purged {$count} passwordless token(s).");

        return self::SUCCESS;
    }
}
