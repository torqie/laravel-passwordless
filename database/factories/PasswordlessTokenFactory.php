<?php

namespace Wiredrhino\LaravelPasswordless\Database\Factories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

/**
 * @extends Factory<PasswordlessToken>
 */
class PasswordlessTokenFactory extends Factory
{
    protected $model = PasswordlessToken::class;

    public function definition(): array
    {
        return [
            'authenticatable_type' => 'App\\Models\\User',
            'authenticatable_id' => 1,
            'token' => hash('sha256', Str::random(64)),
            'type' => 'magic_link',
            'plain_text' => null,
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ];
    }

    public function forAuthenticatable(Authenticatable $model): static
    {
        return $this->state([
            'authenticatable_type' => $model->getMorphClass(),
            'authenticatable_id' => $model->getAuthIdentifier(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }

    public function used(): static
    {
        return $this->state(['used_at' => now()->subSecond()]);
    }

    public function asMagicLink(): static
    {
        return $this->state(['type' => 'magic_link', 'plain_text' => null]);
    }

    public function asLoginCode(): static
    {
        return $this->state(['type' => 'login_code', 'plain_text' => null]);
    }
}
