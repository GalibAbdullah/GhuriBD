@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto max-w-[400px]">
        <div class="card card-pad">
            <h1 class="text-[20px] font-semibold">Welcome back</h1>
            <p class="mb-5 mt-1 text-[13px] text-ink-muted">Log in to continue to GhuriBD</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">
                    <label for="email" class="input-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input @error('email') !border-error @enderror" placeholder="you@example.com" required autofocus autocomplete="username">
                    @error('email')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <div class="flex items-center justify-between">
                        <label for="password" class="input-label">Password</label>
                        <a href="{{ route('password.request') }}" class="text-[12px] font-semibold text-primary">Forgot password?</a>
                    </div>
                    <input id="password" type="password" name="password" class="input @error('password') !border-error @enderror" placeholder="••••••••" required autocomplete="current-password">
                    @error('password')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4 flex items-center gap-2.5">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-line text-primary focus:ring-primary" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember" class="text-[13px] text-ink">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log in</button>
            </form>

            <div class="divider"></div>

            <p class="text-center text-[12.5px] text-ink-muted">
                New to GhuriBD?
                <a href="{{ route('register') }}" class="font-semibold text-primary">Sign up</a>
            </p>
        </div>
    </div>
@endsection