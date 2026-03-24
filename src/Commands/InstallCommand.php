<?php
namespace Torqie\LaravelPasswordless\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
class InstallCommand extends Command
{
    public $signature = "passwordless:install
                        {--force : Overwrite any already-published files}";
    public $description = 'Set up Laravel Passwordless in one step';
    /** @var array<string, array<string, string>> */
    private array $frameworks = [
        'blade'  => ['ext' => 'blade.php', 'label' => 'Blade  (built-in views, no extra JS dependencies)'],
        'vue'    => ['ext' => 'vue',        'label' => 'Vue    (Inertia.js + @inertiajs/vue3)'],
        'react'  => ['ext' => 'jsx',        'label' => 'React  (Inertia.js + @inertiajs/react)'],
        'svelte' => ['ext' => 'svelte',     'label' => 'Svelte (Inertia.js + @inertiajs/svelte)'],
    ];
    public function handle(): int
    {
        $this->displayHeader();
        // Step 1 - Config
        $this->stepHeading('1', 'Configuration');
        if ($this->confirm('Publish config file? (config/passwordless.php)', true)) {
            $this->publishConfig();
        } else {
            $this->components->twoColumnDetail('<fg=yellow>Skipped</>', 'config/passwordless.php');
        }
        // Step 2 - Migrations
        $this->newLine();
        $this->stepHeading('2', 'Migrations');
        if ($this->confirm('Publish migrations?', true)) {
            $this->publishMigrations();
            if ($this->confirm('Run migrations now?', true)) {
                $this->runMigrations();
            } else {
                $this->components->twoColumnDetail('<fg=yellow>Skipped</>', 'Run php artisan migrate when ready');
            }
        } else {
            $this->components->twoColumnDetail('<fg=yellow>Skipped</>', 'No migrations published');
        }
        // Step 3 - Auth flows
        $this->newLine();
        $this->stepHeading('3', 'Authentication Flows');
        $authType = $this->selectAuthType();
        $this->writeEnvKey('PASSWORDLESS_TYPE', $authType);
        $this->components->twoColumnDetail('<fg=green>Set</>', ".env -> PASSWORDLESS_TYPE={$authType}");
        // Step 4 - Frontend framework
        $this->newLine();
        $this->stepHeading('4', 'Frontend Framework');
        $framework = $this->selectFramework();
        // Step 5 - View setup
        $this->newLine();
        $this->stepHeading('5', 'View Setup');
        if ($framework === 'blade') {
            $this->bladeSetup();
        } else {
            $this->inertiaSetup($framework, $authType);
        }
        // Step 6 - Remember me
        $this->newLine();
        $this->stepHeading('6', 'Session Options');
        if ($this->confirm('Enable remember me for persistent login sessions?', false)) {
            $this->writeEnvKey('PASSWORDLESS_REMEMBER', 'true');
            $this->components->twoColumnDetail('<fg=green>Set</>', '.env -> PASSWORDLESS_REMEMBER=true');
        } else {
            $this->components->twoColumnDetail('<fg=gray>Default</>', 'Sessions only (PASSWORDLESS_REMEMBER=false)');
        }
        // Done
        $this->newLine();
        $this->displayModelReminder();
        $this->displayRoutes($authType);
        $this->displayDone();
        return self::SUCCESS;
    }
    // -------------------------------------------------------------------------
    private function publishConfig(): void
    {
        $exists = file_exists(config_path('passwordless.php'));
        $force  = (bool) $this->option('force') ||
            ($exists && $this->confirm('  Config already exists. Overwrite?', false));
        $this->callSilently('vendor:publish', ['--tag' => 'passwordless-config', '--force' => $force]);
        $this->components->twoColumnDetail(
            $exists && ! $force ? '<fg=yellow>Skipped (exists)</>' : '<fg=green>Published</>',
            'config/passwordless.php'
        );
    }
    private function publishMigrations(): void
    {
        $this->callSilently('vendor:publish', [
            '--tag'   => 'passwordless-migrations',
            '--force' => (bool) $this->option('force'),
        ]);
        $this->components->twoColumnDetail('<fg=green>Published</>', 'database/migrations/ (2 files)');
    }
    private function runMigrations(): void
    {
        $this->callSilently('migrate');
        $this->components->twoColumnDetail('<fg=green>Migrated</>', 'passwordless_tokens table ready');
    }
    private function selectAuthType(): string
    {
        $options = [
            'both'       => 'Both magic links and login codes (recommended)',
            'magic_link' => 'Magic links only',
            'login_code' => 'Login codes (OTP) only',
        ];
        $selected = $this->choice(
            'Which authentication flow(s) do you want to use?',
            array_values($options),
            0
        );
        return (string) array_search($selected, $options, true);
    }
    private function selectFramework(): string
    {
        $detected = $this->detectFromPackageJson();
        $labels   = array_column($this->frameworks, 'label');
        if (count($detected) === 1) {
            $fw = $detected[0];
            $this->components->twoColumnDetail('<fg=cyan>Detected</>', "{$fw} (from package.json)");
            if ($this->confirm("Use {$fw}?", true)) {
                return $fw;
            }
        } elseif (count($detected) > 1) {
            $this->components->warn('Multiple JS frameworks detected: '.implode(', ', $detected).'. Please choose one.');
        }
        $selected = $this->choice('Which frontend framework are you using?', $labels, 0);
        foreach ($this->frameworks as $key => $data) {
            if ($data['label'] === $selected) {
                return $key;
            }
        }
        return 'blade';
    }
    private function bladeSetup(): void
    {
        if ($this->confirm('Publish Blade views so you can customise them?', false)) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'laravel-passwordless-views',
                '--force' => (bool) $this->option('force'),
            ]);
            $this->components->twoColumnDetail('<fg=green>Published</>', 'resources/views/vendor/laravel-passwordless/');
        } else {
            $this->components->twoColumnDetail(
                '<fg=gray>Using defaults</>',
                'Package Blade views (publish later with vendor:publish --tag=laravel-passwordless-views)'
            );
        }
    }
    private function inertiaSetup(string $framework, string $authType): void
    {
        $this->components->twoColumnDetail('<fg=cyan>Framework</>', $framework);

        // Inertia is implied by the framework choice — enable it automatically
        $this->writeEnvKey('PASSWORDLESS_INERTIA', 'true');
        $this->components->twoColumnDetail('<fg=green>Set</>', '.env -> PASSWORDLESS_INERTIA=true');

        if ($this->confirm("Publish {$framework} component stubs to resources/js/Pages/Auth/?", true)) {
            $this->publishInertiaStubs($framework, $authType);
        }
    }
    private function publishInertiaStubs(string $framework, string $authType): void
    {
        $ext       = $this->frameworks[$framework]['ext'];
        $stubDir   = __DIR__.'/../../stubs/inertia/'.$framework;
        $outputDir = resource_path('js/Pages/Auth');
        $force     = (bool) $this->option('force');
        File::ensureDirectoryExists($outputDir);
        foreach ($this->resolveComponents($authType) as $component) {
            $src  = "{$stubDir}/{$component}.{$ext}";
            $dest = "{$outputDir}/{$component}.{$ext}";
            if (! file_exists($src)) {
                continue;
            }
            if (file_exists($dest) && ! $force) {
                $this->components->twoColumnDetail(
                    '<fg=yellow>Skipped (exists)</>',
                    "resources/js/Pages/Auth/{$component}.{$ext} (use --force to overwrite)"
                );
                continue;
            }
            File::copy($src, $dest);
            $this->components->twoColumnDetail('<fg=green>Published</>', "resources/js/Pages/Auth/{$component}.{$ext}");
        }
    }
    /** @return array<string> */
    private function resolveComponents(string $authType): array
    {
        $magicLink = ['MagicLinkRequest', 'MagicLinkSent'];
        $loginCode = ['LoginCodeRequest', 'LoginCodeVerify'];
        return match ($authType) {
            'magic_link' => $magicLink,
            'login_code' => $loginCode,
            default      => array_merge($magicLink, $loginCode),
        };
    }
    private function writeEnvKey(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }
        $contents = (string) file_get_contents($envPath);
        if (preg_match("/^{$key}=/m", $contents)) {
            $contents = (string) preg_replace("/^{$key}=.*/m", "{$key}={$value}", $contents);
        } else {
            $contents .= PHP_EOL."{$key}={$value}".PHP_EOL;
        }
        file_put_contents($envPath, $contents);
    }
    /** @return array<string> */
    private function detectFromPackageJson(): array
    {
        $path = base_path('package.json');
        if (! file_exists($path)) {
            return [];
        }
        /** @var array<string, mixed> $json */
        $json    = json_decode((string) file_get_contents($path), true);
        $allDeps = array_merge(
            (array) ($json['dependencies'] ?? []),
            (array) ($json['devDependencies'] ?? [])
        );
        $found = [];
        if (isset($allDeps['vue']))    { $found[] = 'vue'; }
        if (isset($allDeps['react']))  { $found[] = 'react'; }
        if (isset($allDeps['svelte'])) { $found[] = 'svelte'; }
        return $found;
    }
    // -------------------------------------------------------------------------
    private function stepHeading(string $number, string $title): void
    {
        $this->line("  <fg=cyan;options=bold>-- Step {$number}: {$title}</>");
        $this->newLine();
    }
    private function displayHeader(): void
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold> Laravel Passwordless - Installation Wizard </>');
        $this->line('  <fg=gray>Magic links and login codes for your Laravel app.</>');
        $this->newLine();
        $this->line('  <fg=gray>Press Enter to accept the default shown in parentheses.</>');
        $this->newLine();
    }
    private function displayModelReminder(): void
    {
        $this->components->warn('Add the HasPasswordlessAuth trait to your User model:');
        $this->newLine();
        $this->line('  use Torqie\\LaravelPasswordless\\Traits\\HasPasswordlessAuth;');
        $this->newLine();
        $this->line('  class User extends Authenticatable');
        $this->line('  {');
        $this->line('      use HasPasswordlessAuth;');
        $this->line('  }');
        $this->newLine();
    }
    private function displayRoutes(string $authType): void
    {
        $prefix = config('passwordless.routes.prefix', 'auth');
        $this->components->info('Your passwordless routes:');
        $this->newLine();
        if (in_array($authType, ['magic_link', 'both'], true)) {
            $this->line("  GET  /{$prefix}/magic-link");
            $this->line("  POST /{$prefix}/magic-link");
            $this->line("  GET  /{$prefix}/magic-link/{token}");
            $this->newLine();
        }
        if (in_array($authType, ['login_code', 'both'], true)) {
            $this->line("  GET  /{$prefix}/code");
            $this->line("  POST /{$prefix}/code");
            $this->line("  GET  /{$prefix}/code/verify");
            $this->line("  POST /{$prefix}/code/verify");
            $this->newLine();
        }
    }
    private function displayDone(): void
    {
        $this->line('  <fg=green;options=bold>All done! Laravel Passwordless is ready.</>');
        $this->newLine();
        $this->line('  Docs: https://github.com/torqie/laravel-passwordless');
        $this->newLine();
    }
}
