<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Your Code</title>
</head>
<body>
    <h1>Enter your login code</h1>

    <p>We sent a {{ config('passwordless.code.length', 6) }}-digit code to <strong>{{ $email }}</strong>. It expires in {{ config('passwordless.ttl', 15) }} minutes.</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('passwordless.login-code.authenticate') }}">
        @csrf
        <div>
            <label for="code">Login code</label>
            <input
                type="text"
                id="code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="{{ config('passwordless.code.length', 6) }}"
                required
                autofocus
                placeholder="{{ str_repeat('0', config('passwordless.code.length', 6)) }}"
            />
        </div>
        <button type="submit">Verify Code</button>
    </form>

    <p>
        <a href="{{ route('passwordless.login-code.request') }}">Didn't receive it? Request a new code</a>
    </p>
</body>
</html>

