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
     * Hash a plain-text value using SHA-256.
     */
    public function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}
