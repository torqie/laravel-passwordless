<?php

namespace Wiredrhino\LaravelPasswordless\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Wiredrhino\LaravelPasswordless\Contracts\GeneratesLoginCode;
use Wiredrhino\LaravelPasswordless\Models\PasswordlessToken;

class GenerateLoginCodeAction implements GeneratesLoginCode
{
    public function generate(Authenticatable $authenticatable): string
    {
        $charset = (string) config('passwordless.code.charset', '0123456789');
        $length  = (int) config('passwordless.code.length', 6);
        $ttl     = (int) config('passwordless.ttl', 15);

        $plainCode      = $this->generateCode($charset, $length);
        $hashedCode     = hash('sha256', $plainCode);

        PasswordlessToken::create([
            'authenticatable_type' => $authenticatable->getMorphClass(),
            'authenticatable_id'   => $authenticatable->getAuthIdentifier(),
            'token'                => $hashedCode,
            'type'                 => 'login_code',
            'plain_text'           => null,
            'expires_at'           => now()->addMinutes($ttl),
        ]);

        return $plainCode;
    }

    private function generateCode(string $charset, int $length): string
    {
        $charsetLength = strlen($charset);

        if ($charsetLength < 1) {
            throw new \InvalidArgumentException('The passwordless.code.charset config must not be empty.');
        }

        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $charset[random_int(0, $charsetLength - 1)];
        }

        return $code;
    }
}

