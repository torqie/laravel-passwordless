<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magic Link Sent</title>
</head>
<body>
    <h1>Check your email</h1>

    <p>
        If an account exists for that address, we've sent a magic link to sign you in.
        The link will expire in {{ config('passwordless.ttl', 15) }} minutes.
    </p>

    <p>
        <a href="{{ route('passwordless.magic-link.request') }}">Didn't receive it? Try again</a>
    </p>
</body>
</html>

