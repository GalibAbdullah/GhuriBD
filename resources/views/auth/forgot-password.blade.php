@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <div class="mx-auto" style="max-width: 380px;">
        <div class="card">
            <div class="card-body text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-info-subtle text-info" style="width: 44px; height: 44px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="20" height="20"><path d="M21 15a2 2 0 01-2 2H8l-5 4V6a2 2 0 012-2h14a2 2 0 012 2v9z"/></svg>
                </div>
                <h1 class="h5 fw-semibold">Reset your password</h1>
                <p class="mb-3 mt-2 small text-secondary">Enter your email and we'll send a reset link.</p>

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3 text-start">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required autofocus autocomplete="email">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Send reset link</button>
                </form>

                <p class="mt-3">
                    <a href="{{ route('login') }}" class="small fw-semibold link-primary link-underline-opacity-0">← Back to log in</a>
                </p>
            </div>
        </div>
    </div>
@endsection
