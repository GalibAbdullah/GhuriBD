@extends('layouts.guest')

@section('title', 'Sign up')

@section('content')
    <div class="mx-auto" style="max-width: 420px;">
        <div class="card">
            <div class="card-body">
                <h1 class="h4 fw-semibold">Create your account</h1>
                <p class="mb-4 mt-1 small text-secondary">Join as a traveler or list your business as a Travel Partner</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Your name" required autofocus autocomplete="name">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" required autocomplete="email">
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
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Create a password" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create account</button>
                </form>

                <hr class="my-4">

                <p class="text-center small text-secondary">
                    Already have an account?
                    <a href="{{ route('login') }}" class="fw-semibold link-primary link-underline-opacity-0">Log in</a>
                </p>
            </div>
        </div>
    </div>
@endsection
