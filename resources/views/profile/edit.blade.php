@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('sidebar')
    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Dashboard
    </a>
    <a href="{{ route('profile.show') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 rounded active bg-primary-subtle text-primary-emphasis fw-semibold">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
        Profile
    </a>
@endsection

@section('content')
    <div class="mx-auto" style="max-width: 560px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="h6 fw-semibold">Edit Profile</h3>
                    <a href="{{ route('profile.show') }}" class="small fw-semibold link-primary link-underline-opacity-0">Cancel</a>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" value="{{ $user->email }}" class="form-control bg-body-secondary" disabled readonly>
                        <div class="form-text">Email cannot be changed.</div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input id="role" type="text" value="{{ $user->getRoleNames()->implode(', ') ?: 'No role' }}" class="form-control bg-body-secondary" disabled readonly>
                        <div class="form-text">Role cannot be changed.</div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror" placeholder="01XXXXXXXXX">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="form-control @error('date_of_birth') is-invalid @enderror">
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select gender</option>
                            <option value="Male" @selected(old('gender', $user->gender) === 'Male')>Male</option>
                            <option value="Female" @selected(old('gender', $user->gender) === 'Female')>Female</option>
                            <option value="Other" @selected(old('gender', $user->gender) === 'Other')>Other</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Your address">{{ old('address', $user->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Profile Photo</label>
                        <div class="mb-2 d-flex align-items-center gap-3">
                            <img src="{{ $user->profile_photo_url }}" alt="Current profile photo" class="rounded-circle border" style="width: 56px; height: 56px; object-fit: cover;">
                            <span class="small text-secondary">Current photo</span>
                        </div>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/*" class="form-control @error('profile_photo') is-invalid @enderror">
                        <div class="form-text">JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF. Max 5 MB.</div>
                        @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h3 class="mb-4 h6 fw-semibold">Change Password</h3>

                <form method="POST" action="{{ route('profile.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="At least 8 characters" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="••••••••" required autocomplete="new-password">
                        @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
