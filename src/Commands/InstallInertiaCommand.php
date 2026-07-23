<?php

namespace Torqie\LaravelPasswordless\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallInertiaCommand extends Command
{
    public $signature = 'passwordless:install-inertia
                        {--framework= : The frontend framework to use (vue, react, svelte)}
                        {--force : Overwrite existing component files}';

    public $description = 'Publish Inertia component stubs for passwordless authentication';

    /** @var array<string, array<string, string>> */
    private array $extensions = [
        'vue' => ['ext' => 'vue',    'package' => '@inertiajs/vue3'],
        'react' => ['ext' => 'jsx',    'package' => '@inertiajs/react'],
        'svelte' => ['ext' => 'svelte', 'package' => '@inertiajs/svelte'],
    ];

    public function handle(): int
    {
        $framework = $this->resolveFramework();

        if ($framework === null) {
            $this->components->error('Could not determine the frontend framework. Use --framework=vue|react|svelte.');

            return self::FAILURE;
        }

        $this->components->info("Installing Inertia components for <fg=cyan>{$framework}</fg=cyan>...");

        $this->publishStubs($framework);
        $this->printConfigSnippet();

        return self::SUCCESS;
    }

    private function resolveFramework(): ?string
    {
        // 1. Explicit flag
        $flag = $this->option('framework');
        if (is_string($flag)) {
            $flag = strtolower($flag);
            if (array_key_exists($flag, $this->extensions)) {
                return $flag;
            }
            $this->components->error("Unknown framework \"{$flag}\". Choose from: vue, react, svelte.");

            return null;
        }

        // 2. Auto-detect from package.json
        $detected = $this->detectFromPackageJson();

        if (count($detected) === 1) {
            $framework = $detected[0];
            $this->components->info("Detected framework: <fg=cyan>{$framework}</fg=cyan>");

            return $framework;
        }

        if (count($detected) > 1) {
            $this->components->warn('Multiple frameworks detected: '.implode(', ', $detected));
        } else {
            $this->components->warn('Could not auto-detect a frontend framework from package.json.');
        }

        // 3. Interactive prompt
        return $this->components->choice(
            'Which frontend framework are you using?',
            ['vue', 'react', 'svelte']
        );
    }

    /** @return array<string> */
    private function detectFromPackageJson(): array
    {
        $path = base_path('package.json');

        if (! file_exists($path)) {
            return [];
        }

        /** @var array<string, mixed> $json */
        $json = json_decode((string) file_get_contents($path), true);

        $allDeps = array_merge(
            (array) ($json['dependencies'] ?? []),
            (array) ($json['devDependencies'] ?? [])
        );

        $found = [];

        if (isset($allDeps['vue'])) {
            $found[] = 'vue';
        }

        if (isset($allDeps['react'])) {
            $found[] = 'react';
        }

        if (isset($allDeps['svelte'])) {
            $found[] = 'svelte';
        }

        return $found;
    }

    private function publishStubs(string $framework): void
    {
        $stubDir = __DIR__.'/../../stubs/inertia/'.$framework;
        $outputDir = resource_path('js/Pages/Auth');
        $ext = $this->extensions[$framework]['ext'];

        File::ensureDirectoryExists($outputDir);

        $components = ['LoginCodeRequest', 'LoginCodeVerify', 'MagicLinkRequest', 'MagicLinkSent'];
        $published = [];
        $skipped = [];

        foreach ($components as $component) {
            $src = "{$stubDir}/{$component}.{$ext}";
            $dest = "{$outputDir}/{$component}.{$ext}";

            if (! file_exists($src)) {
                continue;
            }

            if (file_exists($dest) && ! $this->option('force')) {
                $skipped[] = $dest;

                continue;
            }

            File::copy($src, $dest);
            $published[] = $dest;
        }

        foreach ($published as $path) {
            $this->components->twoColumnDetail(
                '<fg=green>Published</>',
                str_replace(base_path().'/', '', $path)
            );
        }

        foreach ($skipped as $path) {
            $this->components->twoColumnDetail(
                '<fg=yellow>Skipped (already exists)</>',
                str_replace(base_path().'/', '', $path)
            );
        }

        if (! empty($skipped)) {
            $this->newLine();
            $this->line('  Use <fg=cyan>--force</> to overwrite existing files.');
        }
    }

    private function printConfigSnippet(): void
    {
        $this->newLine();
        $this->components->info('Add the following to your <fg=cyan>config/passwordless.php</>:');
        $this->newLine();
        $this->line(<<<'PHP'
  'inertia' => true,

  'components' => [
      'magic_link_request' => 'Auth/MagicLinkRequest',
      'magic_link_sent'    => 'Auth/MagicLinkSent',
      'login_code_request' => 'Auth/LoginCodeRequest',
      'login_code_verify'  => 'Auth/LoginCodeVerify',
  ],
PHP);
        $this->newLine();
        $this->components->info('Or set <fg=cyan>PASSWORDLESS_INERTIA=true</> in your <fg=cyan>.env</> file.');
        $this->newLine();
    }
}
