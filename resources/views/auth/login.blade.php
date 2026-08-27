@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto" style="max-width: 400px;">
        <div class="card">
            <div class="card-body">
                <h1 class="h4 fw-semibold">Welcome back</h1>
                <p class="mb-4 mt-1 small text-secondary">Log in to continue to GhuriBD</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required autofocus autocomplete="username">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="password" class="form-label">Password</label>
                            <a href="{{ route('password.request') }}" class="small fw-semibold link-primary link-underline-opacity-0">Forgot password?</a>
                        </div>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input id="remember" type="checkbox" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="form-check-label small">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Log in</button>
                </form>

                <hr class="my-4">

                <p class="text-center small text-secondary">
                    New to GhuriBD?
                    <a href="{{ route('register') }}" class="fw-semibold link-primary link-underline-opacity-0">Sign up</a>
                </p>
            </div>
        </div>
    </div>
@endsection
