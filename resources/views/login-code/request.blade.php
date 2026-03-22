@extends('laravel-passwordless::layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="icon-wrap">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <h1 class="card-title">Sign in with a code</h1>
    <p class="card-subtitle">
        Enter your email and we'll send a {{ config('passwordless.code.length', 6) }}-digit code — no password needed.
    </p>

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('passwordless.login-code.send') }}">
        @csrf
        <div class="field">
            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                placeholder="you@example.com"
            />
        </div>
        <button type="submit" class="btn">Send Code</button>
    </form>
@endsection

