<?php

namespace App\Http\Controllers;

use App\Enums\ResortAmenity;
use App\Enums\ResortStatus;
use App\Enums\UserRole;
use App\Http\Requests\ResortRequest;
use App\Models\Resort;
use App\Models\ResortImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ResortController extends Controller
{
    /**
     * List resorts. Travel Partners see only their own; Admins see everyone's;
     * Travelers browse the public, active-only listing.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Resort::class);

        $user = $request->user();
        $isAdmin = $user->hasRole(UserRole::ADMIN->value);
        $isTraveler = $user->hasRole(UserRole::TRAVELER->value);
        $search = $request->string('search')->trim()->value();

        $query = match (true) {
            $isAdmin => Resort::query()->with('user'),
            $isTraveler => Resort::query()->where('status', ResortStatus::ACTIVE->value),
            default => $user->resorts(),
        };

        $resorts = $query
            ->withCount('rooms')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhere('division', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($isTraveler) {
            return view('traveler.resorts.index', [
                'resorts' => $resorts,
                'search' => $search,
            ]);
        }

        $countQuery = fn () => $isAdmin ? Resort::query() : $user->resorts();

        return view('resorts.index', [
            'resorts' => $resorts,
            'isAdmin' => $isAdmin,
            'search' => $search,
            'stats' => [
                'total' => $countQuery()->count(),
                'active' => $countQuery()->where('status', ResortStatus::ACTIVE->value)->count(),
                'inactive' => $countQuery()->where('status', ResortStatus::INACTIVE->value)->count(),
            ],
        ]);
    }

    /**
     * Show the form to create a new resort listing.
     */
    public function create(): View
    {
        Gate::authorize('create', Resort::class);

        return view('resorts.create', [
            'divisions' => array_keys(config('bangladesh.divisions')),
            'districtsByDivision' => config('bangladesh.divisions'),
            'amenities' => ResortAmenity::values(),
        ]);
    }

    /**
     * Store a new resort listing for the authenticated Travel Partner.
     */
    public function store(ResortRequest $request): RedirectResponse
    {
        Gate::authorize('create', Resort::class);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['amenities'] = $request->input('amenities', []);
        $data['cover_image'] = $request->file('cover_image')->store('resorts/cover', 'public');

        $resort = $request->user()->resorts()->create($data);

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $resort->images()->create([
                'image_path' => $galleryImage->store('resorts/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.resorts.show', $resort)
            ->with('status', 'Resort created successfully.');
    }

    /**
     * Show a single resort. Accessible by its owning Travel Partner and by Admins.
     */
    public function show(Request $request, Resort $resort): View
    {
        Gate::authorize('view', $resort);

        $resort = $resort->load(['user', 'images'])->loadCount('rooms');

        if ($request->user()->hasRole(UserRole::TRAVELER->value)) {
            return view('traveler.resorts.show', ['resort' => $resort]);
        }

        return view('resorts.show', [
            'resort' => $resort,
            'isAdmin' => $request->user()->hasRole(UserRole::ADMIN->value),
        ]);
    }

    /**
     * Show the form to edit an existing resort listing.
     */
    public function edit(Resort $resort): View
    {
        Gate::authorize('update', $resort);

        return view('resorts.edit', [
            'resort' => $resort->load('images'),
            'divisions' => array_keys(config('bangladesh.divisions')),
            'districtsByDivision' => config('bangladesh.divisions'),
            'amenities' => ResortAmenity::values(),
        ]);
    }

    /**
     * Update an existing resort listing.
     */
    public function update(ResortRequest $request, Resort $resort): RedirectResponse
    {
        Gate::authorize('update', $resort);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['amenities'] = $request->input('amenities', []);

        if ($request->hasFile('cover_image')) {
            Storage::disk('public')->delete($resort->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('resorts/cover', 'public');
        }

        $resort->update($data);

        // Scope the delete to this resort's own images so a tampered ID can't remove someone else's file.
        ResortImage::query()
            ->whereIn('id', $request->input('remove_gallery_images', []))
            ->where('resort_id', $resort->id)
            ->get()
            ->each->delete();

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $resort->images()->create([
                'image_path' => $galleryImage->store('resorts/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.resorts.show', $resort)
            ->with('status', 'Resort updated successfully.');
    }

    /**
     * Delete a resort listing along with its cover and gallery images.
     */
    public function destroy(Resort $resort): RedirectResponse
    {
        Gate::authorize('delete', $resort);

        $resort->delete();

        return redirect()
            ->route('partner.resorts.index')
            ->with('status', 'Resort deleted successfully.');
    }
}
