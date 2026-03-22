@extends('laravel-passwordless::layouts.auth')

@section('title', 'Check Your Email')

@section('content')
    <div class="success-icon">
        <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>
    </div>

    <h1 class="card-title">Check your inbox</h1>
    <p class="card-subtitle">
        If an account exists for that address, we've sent a magic link to sign you in.
        The link will expire in {{ config('passwordless.ttl', 15) }} minutes.
    </p>

    <hr class="divider">

    <p class="footer-link">
        Didn't receive it? <a href="{{ route('passwordless.magic-link.request') }}">Try again</a>
    </p>
@endsection

