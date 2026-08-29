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
 * @property string|null $salt
 * @property string $type
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 *
 * @method static Builder<PasswordlessToken> valid()
 * @method static Builder<PasswordlessToken> unused()
 * @method static Builder<PasswordlessToken> ofType(string $type)
 */
class PasswordlessToken extends Model
{
    /** @use HasFactory<PasswordlessTokenFactory> */
    use HasFactory;

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'token',
        'salt',
        'type',
        'expires_at',
        'used_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'token',
        'salt',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /** @return MorphTo<Model, $this> */
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

    /**
     * Retire a token that has served its purpose.
     *
     * Login codes delete themselves. A code is short and drawn from a small
     * charset, so every retained row is another chance for a future code to
     * collide on the unique `token` index — and a consumed row has no remaining
     * function, since single-use is enforced just as well by its absence. The
     * user-facing failure message is identical either way ("incorrect or
     * expired"), deliberately, so nothing diagnosable is lost; a successful
     * login is already reported by the UserAuthenticatedPasswordlessly event.
     *
     * Magic links keep marking `used_at` instead. They are 64 random characters,
     * so retention costs nothing on the collision front, and `used_at` stays
     * genuinely useful — email scanners and link prefetchers routinely fetch a
     * magic link before the human clicks it, and that is worth being able to see.
     */
    public function consume(): bool
    {
        if ($this->type === 'login_code') {
            return (bool) $this->delete();
        }

        return $this->markUsed();
    }

    public function markUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
