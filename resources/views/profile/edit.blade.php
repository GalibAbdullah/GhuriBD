@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Dashboard
    </a>
    <a href="{{ route('profile.show') }}" class="nav-item active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        Profile
    </a>
@endsection

@section('content')
    <div class="mx-auto max-w-[560px]">
        <div class="card card-pad">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-[16px] font-semibold">Edit Profile</h3>
                <a href="{{ route('profile.show') }}" class="text-[12.5px] font-semibold text-primary">Cancel</a>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="input-group">
                    <label for="name" class="input-label">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="input @error('name') !border-error @enderror" required autofocus>
                    @error('name')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="email" class="input-label">Email</label>
                    <input id="email" type="email" value="{{ $user->email }}" class="input bg-surface" disabled readonly>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Email cannot be changed.</p>
                </div>

                <div class="input-group">
                    <label for="role" class="input-label">Role</label>
                    <input id="role" type="text" value="{{ $user->getRoleNames()->implode(', ') ?: 'No role' }}" class="input bg-surface" disabled readonly>
                    <p class="mt-1 text-[11.5px] text-ink-muted">Role cannot be changed.</p>
                </div>

                <div class="input-group">
                    <label for="phone" class="input-label">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="input @error('phone') !border-error @enderror" placeholder="01XXXXXXXXX">
                    @error('phone')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="date_of_birth" class="input-label">Date of Birth</label>
                    <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="input @error('date_of_birth') !border-error @enderror">
                    @error('date_of_birth')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="gender" class="input-label">Gender</label>
                    <select id="gender" name="gender" class="input @error('gender') !border-error @enderror">
                        <option value="">Select gender</option>
                        <option value="Male" @selected(old('gender', $user->gender) === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender', $user->gender) === 'Female')>Female</option>
                        <option value="Other" @selected(old('gender', $user->gender) === 'Other')>Other</option>
                    </select>
                    @error('gender')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="address" class="input-label">Address</label>
                    <textarea id="address" name="address" rows="3" class="input @error('address') !border-error @enderror" placeholder="Your address">{{ old('address', $user->address) }}</textarea>
                    @error('address')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="profile_photo" class="input-label">Profile Photo</label>
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ $user->profile_photo_url }}" alt="Current profile photo" class="h-14 w-14 rounded-full border border-line object-cover">
                        <span class="text-[11.5px] text-ink-muted">Current photo</span>
                    </div>
                    <input id="profile_photo" type="file" name="profile_photo" accept="image/*" class="input @error('profile_photo') !border-error @enderror">
                    <p class="mt-1 text-[11.5px] text-ink-muted">JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF. Max 5 MB.</p>
                    @error('profile_photo')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </form>
        </div>

        <div class="card card-pad mt-4">
            <h3 class="mb-5 text-[16px] font-semibold">Change Password</h3>

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="input-group">
                    <label for="current_password" class="input-label">Current Password</label>
                    <input id="current_password" type="password" name="current_password" class="input @error('current_password') !border-error @enderror" placeholder="••••••••" required autocomplete="current-password">
                    @error('current_password')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">New Password</label>
                    <input id="password" type="password" name="password" class="input @error('password') !border-error @enderror" placeholder="At least 8 characters" required autocomplete="new-password">
                    @error('password')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <div class="input-group">
                    <label for="password_confirmation" class="input-label">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input @error('password_confirmation') !border-error @enderror" placeholder="••••••••" required autocomplete="new-password">
                    @error('password_confirmation')<p class="mt-1 text-[11.5px] font-medium text-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Password</button>
            </form>
        </div>
    </div>
@endsection