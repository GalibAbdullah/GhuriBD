@extends('layouts.guest')

@section('content')
    <h2 class="h3 fw-bold mb-2">Create account</h2>
    <p class="text-muted mb-4">Register to book resorts, packages, and guides later.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Account type</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $defaultRole) === $role->value)>{{ $role->value }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between align-items-sm-center">
            <a href="{{ route('login') }}" class="link-primary text-decoration-none">Already registered?</a>
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>
@endsection