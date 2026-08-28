<?php

namespace App\Http\Controllers;

use App\Enums\RoomAmenity;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\UserRole;
use App\Http\Requests\RoomRequest;
use App\Models\Resort;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * List the rooms of a single resort. Travel Partners must own the resort; Admins may view any resort's rooms.
     */
    public function index(Request $request, Resort $resort): View
    {
        Gate::authorize('view', $resort);

        $search = $request->string('search')->trim()->value();

        $rooms = $resort->rooms()
            ->when($search, fn ($query) => $query->where('room_name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        if ($request->user()->hasRole(UserRole::TRAVELER->value)) {
            return view('traveler.rooms.index', [
                'resort' => $resort,
                'rooms' => $rooms,
                'search' => $search,
            ]);
        }

        return view('rooms.index', [
            'resort' => $resort,
            'rooms' => $rooms,
            'search' => $search,
            'isAdmin' => $request->user()->hasRole(UserRole::ADMIN->value),
        ]);
    }

    /**
     * Show the form to add a new room to a resort.
     */
    public function create(Resort $resort): View
    {
        Gate::authorize('create', [Room::class, $resort]);

        return view('rooms.create', [
            'resort' => $resort,
            'roomTypes' => RoomType::values(),
            'amenities' => RoomAmenity::values(),
            'statuses' => RoomStatus::values(),
        ]);
    }

    /**
     * Store a new room for the given resort.
     */
    public function store(RoomRequest $request, Resort $resort): RedirectResponse
    {
        Gate::authorize('create', [Room::class, $resort]);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['amenities'] = $request->input('amenities', []);
        $data['cover_image'] = $request->file('cover_image')->store('rooms/cover', 'public');

        $room = $resort->rooms()->create($data);

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $room->images()->create([
                'image_path' => $galleryImage->store('rooms/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.resorts.rooms.show', [$resort, $room])
            ->with('status', 'Room created successfully.');
    }

    /**
     * Show a single room. Accessible by the owning Travel Partner and by Admins.
     */
    public function show(Request $request, Resort $resort, Room $room): View
    {
        abort_unless($room->resort_id === $resort->id, 404);

        Gate::authorize('view', $room);

        $room = $room->load('images');

        if ($request->user()->hasRole(UserRole::TRAVELER->value)) {
            return view('traveler.rooms.show', ['resort' => $resort, 'room' => $room]);
        }

        return view('rooms.show', [
            'resort' => $resort,
            'room' => $room,
            'isAdmin' => $request->user()->hasRole(UserRole::ADMIN->value),
        ]);
    }

    /**
     * Show the form to edit an existing room.
     */
    public function edit(Resort $resort, Room $room): View
    {
        abort_unless($room->resort_id === $resort->id, 404);

        Gate::authorize('update', $room);

        return view('rooms.edit', [
            'resort' => $resort,
            'room' => $room->load('images'),
            'roomTypes' => RoomType::values(),
            'amenities' => RoomAmenity::values(),
            'statuses' => RoomStatus::values(),
        ]);
    }

    /**
     * Update an existing room.
     */
    public function update(RoomRequest $request, Resort $resort, Room $room): RedirectResponse
    {
        abort_unless($room->resort_id === $resort->id, 404);

        Gate::authorize('update', $room);

        $data = $request->safe()->except(['cover_image', 'gallery_images', 'remove_gallery_images']);
        $data['amenities'] = $request->input('amenities', []);

        if ($request->hasFile('cover_image')) {
            Storage::disk('public')->delete($room->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('rooms/cover', 'public');
        }

        $room->update($data);

        // Scope the delete to this room's own images so a tampered ID can't remove someone else's file.
        RoomImage::query()
            ->whereIn('id', $request->input('remove_gallery_images', []))
            ->where('room_id', $room->id)
            ->get()
            ->each->delete();

        foreach ($request->file('gallery_images', []) as $galleryImage) {
            $room->images()->create([
                'image_path' => $galleryImage->store('rooms/gallery', 'public'),
            ]);
        }

        return redirect()
            ->route('partner.resorts.rooms.show', [$resort, $room])
            ->with('status', 'Room updated successfully.');
    }

    /**
     * Delete a room along with its cover and gallery images.
     */
    public function destroy(Resort $resort, Room $room): RedirectResponse
    {
        abort_unless($room->resort_id === $resort->id, 404);

        Gate::authorize('delete', $room);

        $room->delete();

        return redirect()
            ->route('partner.resorts.rooms.index', $resort)
            ->with('status', 'Room deleted successfully.');
    }
}
