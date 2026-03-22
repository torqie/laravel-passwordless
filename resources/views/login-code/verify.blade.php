@extends('laravel-passwordless::layouts.auth')

@section('title', 'Enter Your Code')

@section('content')
    <div class="icon-wrap">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
    </div>

    <h1 class="card-title">Enter your login code</h1>
    <p class="card-subtitle">
        We sent a {{ config('passwordless.code.length', 6) }}-digit code to <strong>{{ $email }}</strong>.
        It expires in {{ config('passwordless.ttl', 15) }} minutes.
    </p>

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('passwordless.login-code.authenticate') }}">
        @csrf
        <div class="field">
            <label for="code">Login code</label>
            <input
                type="text"
                id="code"
                name="code"
                class="code-input"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="{{ config('passwordless.code.length', 6) }}"
                required
                autofocus
                placeholder="{{ str_repeat('·', config('passwordless.code.length', 6)) }}"
            />
            <p class="hint">Enter the {{ config('passwordless.code.length', 6) }}-digit code from your email.</p>
        </div>
        <button type="submit" class="btn">Verify Code</button>
    </form>

    <hr class="divider">

    <p class="footer-link">
        Didn't receive it? <a href="{{ route('passwordless.login-code.request') }}">Request a new code</a>
    </p>
@endsection

