<?php

namespace Torqie\LaravelPasswordless\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Torqie\LaravelPasswordless\Models\PasswordlessToken;

trait HasPasswordlessAuth
{
    public function passwordlessTokens(): MorphMany
    {
        return $this->morphMany(PasswordlessToken::class, 'authenticatable');
    }

    public function validPasswordlessTokens(): MorphMany
    {
        return $this->passwordlessTokens()->scopes(['valid']);
    }

    public function invalidatePasswordlessTokens(?string $type = null): void
    {
        $query = $this->passwordlessTokens()->whereNull('used_at');

        if ($type !== null) {
            $query->ofType($type);
        }

        $query->update(['used_at' => now()]);
    }
}
