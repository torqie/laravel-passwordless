<?php

namespace Torqie\LaravelPasswordless\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Torqie\LaravelPasswordless\Database\Factories\PasswordlessTokenFactory;

/**
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 *
 * @method static Builder<PasswordlessToken> valid()
 * @method static Builder<PasswordlessToken> unused()
 * @method static Builder<PasswordlessToken> ofType(string $type)
 */
class PasswordlessToken extends Model
{
    /** @use HasFactory<\Torqie\LaravelPasswordless\Database\Factories\PasswordlessTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'token',
        'type',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param  Builder<PasswordlessToken>  $query
     * @return Builder<PasswordlessToken>
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    /** @param  Builder<PasswordlessToken>  $query
     * @return Builder<PasswordlessToken>
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->whereNull('used_at');
    }

    /** @param  Builder<PasswordlessToken>  $query
     * @return Builder<PasswordlessToken>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isUsed();
    }

    public function markUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
