<?php

namespace Torqie\LaravelPasswordless\Support;

use Illuminate\Support\Str;

class TokenGenerator
{
    /**
     * Generate a cryptographically random URL-safe token.
     *
     * @return array{plain: string, hashed: string}
     */
    public function makeToken(): array
    {
        $plain = Str::random(64);

        return [
            'plain' => $plain,
            'hashed' => $this->hash($plain),
        ];
    }

    /**
     * Generate a random code from the given charset.
     *
     * @throws \InvalidArgumentException
     */
    public function makeCode(string $charset, int $length): string
    {
        $charsetLength = strlen($charset);

        if ($charsetLength < 1) {
            throw new \InvalidArgumentException(
                'The passwordless.code.charset config must not be empty.'
            );
        }

        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $charset[random_int(0, $charsetLength - 1)];
        }

        return $code;
    }

    /**
     * Generate a random salt for a low-entropy secret.
     *
     * Login codes are short and drawn from a small charset, so an unsalted digest
     * is both brute-forceable from a database dump and prone to colliding on the
     * unique index. A per-row salt fixes both.
     */
    public function makeSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Hash a plain-text value using SHA-256, optionally salted.
     *
     * Passing null reproduces the original unsalted digest, which is what magic
     * links use (they are looked up BY hash, so they cannot carry a random salt)
     * and what pre-salt rows in the database still contain.
     */
    public function hash(string $value, ?string $salt = null): string
    {
        return hash('sha256', ($salt ?? '').$value);
    }
}
