@extends('layouts.guest')

@section('content')
    <h2 class="h3 fw-bold mb-2">Reset password</h2>
    <p class="text-muted mb-4">Enter your email address and we will send a password reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center">
            <a href="{{ route('login') }}" class="link-primary text-decoration-none">Back to login</a>
            <button type="submit" class="btn btn-primary">Email password reset link</button>
        </div>
    </form>
@endsection