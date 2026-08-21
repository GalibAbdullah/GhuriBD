@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <div class="mx-auto max-w-[400px]">
        <div class="card card-pad">
            <h1 class="text-[20px] font-semibold">Set a new password</h1>
            <p class="mb-5 mt-1 text-[13px] text-ink-muted">Choose a strong password for your account.</p>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="input-group">
                    <label for="email" class="input-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="input @error('email') !border-error @enderror" placeholder="you@example.com" required autofocus autocomplete="username">
                    @error('email')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">New password</label>
                    <input id="password" type="password" name="password" class="input @error('password') !border-error @enderror" placeholder="••••••••" required autocomplete="new-password">
                    @error('password')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password_confirmation" class="input-label">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" placeholder="••••••••" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Reset password</button>
            </form>

            <p class="mt-4 text-center text-[12.5px] text-ink-muted">
                Remembered it?
                <a href="{{ route('login') }}" class="font-semibold text-primary">Back to log in</a>
            </p>
        </div>
    </div>
@endsection