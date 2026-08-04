@extends('layouts.guest')

@section('content')
    <h2 class="h3 fw-bold mb-2">Sign in</h2>
    <p class="text-muted mb-4">Use your GhuriBD account to continue.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check mb-3">
            <input id="remember" type="checkbox" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center justify-content-between">
            <a href="{{ route('password.request') }}" class="link-primary text-decoration-none">Forgot your password?</a>
            <div class="d-flex gap-2">
                <a href="{{ route('register') }}" class="btn btn-outline-secondary">Create account</a>
                <button type="submit" class="btn btn-primary">Log in</button>
            </div>
        </div>
    </form>
@endsection