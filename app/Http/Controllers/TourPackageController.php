<?php

namespace App\Http\Controllers;

use App\Enums\TourPackageService;
use App\Enums\TourPackageStatus;
use App\Enums\UserRole;
use App\Http\Requests\TourPackageRequest;
use App\Models\TourPackage;
use App\Models\TourPackageImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TourPackageController extends Controller
{
    /**
     * List tour packages. Travel Partners see only their own; Admins see everyone's;
     * Travelers browse the public, active-only listing.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', TourPackage::class);

        $user = $request->user();
        $isAdmin = $user->hasRole(UserRole::ADMIN->value);
        $isTraveler = $user->hasRole(UserRole::TRAVELER->value);
        $search = $request->string('search')->trim()->value();

        $query = match (true) {
            $isAdmin => TourPackage::query()->with('user'),
            $isTraveler => TourPackage::query()->where('status', TourPackageStatus::ACTIVE->value),
            default => $user->tourPackages(),
        };

        $tourPackages = $query
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($isTraveler) {
            return view('traveler.tour-packages.index', [
                'tourPackages' => $tourPackages,
                'search' => $search,
            ]);
        }

        $countQuery = fn () => $isAdmin ? TourPackage::query() : $user->tourPackages();

        return view('tour-packages.index', [
            'tourPackages' => $tourPackages,
            'isAdmin' => $isAdmin,
            'search' => $search,
            'stats' => [
                'total' => $countQuery()->count(),
                'active' => $countQuery()->where('status', TourPackageStatus::ACTIVE->value)->count(),
                'inactive' => $countQuery()->where('status', TourPackageStatus::INACTIVE->value)->count(),
            ],
        ]);
    }

    /**
     * Show the form to create a new tour package.
     */
    public function create(): View
    {
        Gate::authorize('create', TourPackage::class);

        return view('tour-packages.create', [
            'divisions' => array_keys(config('bangladesh.divisions')),
            'districtsByDivision' => config('bangladesh.divisions'),
            'services' => TourPackageService::values(),
            'statuses' => TourPackageStatus::values(),
        ]);
    }

    /**
     * Store a new tour package for the authenticated Travel Partner.
     */
    public function store(TourPackageRequest $request): RedirectResponse
    {
        Gate::authorize('create', TourPackage::class);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['included_services'] = $request->input('included_services', []);
        $data['excluded_services'] = $request->input('excluded_services', []);
        $data['cover_image'] = $request->file('cover_image')->store('tour-packages/cover', 'public');

        $tourPackage = $request->user()->tourPackages()->create($data);

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $tourPackage->images()->create([
                'image_path' => $galleryImage->store('tour-packages/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.packages.show', $tourPackage)
            ->with('status', 'Tour package created successfully.');
    }

    /**
     * Show a single tour package. Accessible by its owning Travel Partner and by Admins.
     */
    public function show(Request $request, TourPackage $package): View
    {
        Gate::authorize('view', $package);

        $package = $package->load(['user', 'images']);

        if ($request->user()->hasRole(UserRole::TRAVELER->value)) {
            return view('traveler.tour-packages.show', ['tourPackage' => $package]);
        }

        return view('tour-packages.show', [
            'tourPackage' => $package,
            'isAdmin' => $request->user()->hasRole(UserRole::ADMIN->value),
        ]);
    }

    /**
     * Show the form to edit an existing tour package.
     */
    public function edit(TourPackage $package): View
    {
        Gate::authorize('update', $package);

        return view('tour-packages.edit', [
            'tourPackage' => $package->load('images'),
            'divisions' => array_keys(config('bangladesh.divisions')),
            'districtsByDivision' => config('bangladesh.divisions'),
            'services' => TourPackageService::values(),
            'statuses' => TourPackageStatus::values(),
        ]);
    }

    /**
     * Update an existing tour package.
     */
    public function update(TourPackageRequest $request, TourPackage $package): RedirectResponse
    {
        Gate::authorize('update', $package);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['included_services'] = $request->input('included_services', []);
        $data['excluded_services'] = $request->input('excluded_services', []);

        if ($request->hasFile('cover_image')) {
            Storage::disk('public')->delete($package->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('tour-packages/cover', 'public');
        }

        $package->update($data);

        // Scope the delete to this package's own images so a tampered ID can't remove someone else's file.
        TourPackageImage::query()
            ->whereIn('id', $request->input('remove_gallery_images', []))
            ->where('tour_package_id', $package->id)
            ->get()
            ->each->delete();

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $package->images()->create([
                'image_path' => $galleryImage->store('tour-packages/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.packages.show', $package)
            ->with('status', 'Tour package updated successfully.');
    }

    /**
     * Delete a tour package along with its cover and gallery images.
     */
    public function destroy(TourPackage $package): RedirectResponse
    {
        Gate::authorize('delete', $package);

        $package->delete();

        return redirect()
            ->route('partner.packages.index')
            ->with('status', 'Tour package deleted successfully.');
    }
}
