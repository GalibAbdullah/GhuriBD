@extends('layouts.guest')

@section('title', 'Forgot password')

@section('content')
    <div class="mx-auto max-w-[380px]">
        <div class="card card-pad text-center">
            <div class="mx-auto mb-3 grid h-11 w-11 place-items-center rounded-full bg-info-tint text-info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5"><path d="M21 15a2 2 0 01-2 2H8l-5 4V6a2 2 0 012-2h14a2 2 0 012 2v9z"/></svg>
            </div>
            <h1 class="text-[18px] font-semibold">Reset your password</h1>
            <p class="mb-4 mt-2 text-[13px] text-ink-muted">Enter your email and we'll send a reset link.</p>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="input-group">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input @error('email') !border-error @enderror" placeholder="you@example.com" required autofocus autocomplete="email">
                    @error('email')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Send reset link</button>
            </form>

            <p class="mt-3.5">
                <a href="{{ route('login') }}" class="text-[12.5px] font-semibold text-primary">← Back to log in</a>
            </p>
        </div>
    </div>
@endsection