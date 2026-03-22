@extends('laravel-passwordless::layouts.auth')

@section('title', 'Sign In')

@section('content')
    <div class="icon-wrap">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
            <path d="M9 18h6"/><path d="M10 22h4"/>
        </svg>
    </div>

    <h1 class="card-title">Sign in with a magic link</h1>
    <p class="card-subtitle">
        Enter your email and we'll send a one-click sign-in link — no password needed.
    </p>

    @if ($errors->any())
        <ul class="errors">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('passwordless.magic-link.send') }}">
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
        <button type="submit" class="btn">Send Magic Link</button>
    </form>
@endsection

