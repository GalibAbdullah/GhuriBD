@extends('layouts.guest')

@section('title', 'Sign up')

@section('content')
    <div class="mx-auto max-w-[420px]">
        <div class="card card-pad">
            <h1 class="text-[20px] font-semibold">Create your account</h1>
            <p class="mb-5 mt-1 text-[13px] text-ink-muted">Join as a traveler or list your business as a Travel Partner</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="input-group">
                    <label for="name" class="input-label">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="input @error('name') !border-error @enderror" placeholder="Your name" required autofocus autocomplete="name">
                    @error('name')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="email" class="input-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input @error('email') !border-error @enderror" placeholder="you@example.com" required autocomplete="email">
                    @error('email')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="role" class="input-label">Account type</label>
                    <select id="role" name="role" class="input @error('role') !border-error @enderror" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $defaultRole) === $role->value)>{{ $role->value }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Password</label>
                    <input id="password" type="password" name="password" class="input @error('password') !border-error @enderror" placeholder="Create a password" required autocomplete="new-password">
                    @error('password')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password_confirmation" class="input-label">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" placeholder="Confirm password" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create account</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-[12.5px] text-ink-muted">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-primary">Log in</a>
            </p>
        </div>
    </div>
@endsection