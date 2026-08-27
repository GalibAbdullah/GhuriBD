@extends('layouts.guest')

@section('title', 'Reset password')

@section('content')
    <div class="mx-auto" style="max-width: 400px;">
        <div class="card">
            <div class="card-body">
                <h1 class="h4 fw-semibold">Set a new password</h1>
                <p class="mb-4 mt-1 small text-secondary">Choose a strong password for your account.</p>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required autofocus autocomplete="username">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Reset password</button>
                </form>

                <p class="mt-4 text-center small text-secondary">
                    Remembered it?
                    <a href="{{ route('login') }}" class="fw-semibold link-primary link-underline-opacity-0">Back to log in</a>
                </p>
            </div>
        </div>
    </div>
@endsection
