<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(): View
    {
        return view('profile.show', [
            'user' => request()->user(),
        ]);
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => request()->user(),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        // Handle profile photo upload via Laravel Storage (public disk).
        if ($request->hasFile('profile_photo')) {
            // Delete the old photo if one exists.
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $data['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('status', 'Password updated successfully.');
    }
}
