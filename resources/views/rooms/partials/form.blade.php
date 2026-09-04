@php
    $selectedAmenities = old('amenities', $room->amenities ?? []);
@endphp

<h4 class="h6 fw-semibold mb-3">Basic Information</h4>

<div class="mb-3">
    <label for="room_name" class="form-label">Room Name</label>
    <input id="room_name" type="text" name="room_name" value="{{ old('room_name', $room->room_name ?? '') }}" class="form-control @error('room_name') is-invalid @enderror" placeholder="e.g. Ocean View Deluxe" required autofocus>
    @error('room_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <label for="room_type" class="form-label">Room Type</label>
        <select id="room_type" name="room_type" class="form-select @error('room_type') is-invalid @enderror" required>
            <option value="">Select room type</option>
            @foreach ($roomTypes as $type)
                <option value="{{ $type }}" @selected(old('room_type', $room->room_type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('room_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $room->status ?? 'Available') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Describe this room" required>{{ old('description', $room->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Pricing</h4>

<div class="mb-4">
    <label for="price_per_night" class="form-label">Price Per Night</label>
    <div class="input-group" style="max-width: 260px;">
        <span class="input-group-text">৳</span>
        <input id="price_per_night" type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night ?? '') }}" class="form-control @error('price_per_night') is-invalid @enderror" required>
    </div>
    @error('price_per_night')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Capacity</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <label for="capacity" class="form-label">Capacity (guests)</label>
        <input id="capacity" type="number" min="1" name="capacity" value="{{ old('capacity', $room->capacity ?? '') }}" class="form-control @error('capacity') is-invalid @enderror" required>
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6 col-md-3">
        <label for="total_rooms" class="form-label">Total Rooms</label>
        <input id="total_rooms" type="number" min="1" name="total_rooms" value="{{ old('total_rooms', $room->total_rooms ?? '') }}" class="form-control @error('total_rooms') is-invalid @enderror" required>
        @error('total_rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6 col-md-3">
        <label for="available_rooms" class="form-label">Available Rooms</label>
        <input id="available_rooms" type="number" min="0" name="available_rooms" value="{{ old('available_rooms', $room->available_rooms ?? '') }}" class="form-control @error('available_rooms') is-invalid @enderror" required>
        @error('available_rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6 col-md-3">
        <label for="bed_type" class="form-label">Bed Type</label>
        <input id="bed_type" type="text" name="bed_type" value="{{ old('bed_type', $room->bed_type ?? '') }}" class="form-control @error('bed_type') is-invalid @enderror" placeholder="e.g. King" required>
        @error('bed_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-4">
    <label for="room_size" class="form-label">Room Size</label>
    <input id="room_size" type="text" name="room_size" value="{{ old('room_size', $room->room_size ?? '') }}" class="form-control @error('room_size') is-invalid @enderror" placeholder="e.g. 300 sq ft" style="max-width: 260px;" required>
    @error('room_size')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Amenities</h4>

<div class="row row-cols-2 row-cols-sm-3 g-2 mb-4">
    @foreach ($amenities as $index => $amenity)
        <div class="col">
            <div class="form-check">
                <input type="checkbox" name="amenities[]" value="{{ $amenity }}" id="amenity_{{ $index }}" class="form-check-input" @checked(in_array($amenity, $selectedAmenities, true))>
                <label for="amenity_{{ $index }}" class="form-check-label small">{{ $amenity }}</label>
            </div>
        </div>
    @endforeach
</div>
@error('amenities')<div class="text-danger small fw-medium mb-4">{{ $message }}</div>@enderror

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Images</h4>

<div class="mb-3">
    <label for="cover_image" class="form-label">Cover Image</label>
    <div class="mb-2 {{ isset($room) ? '' : 'd-none' }}" id="coverPreviewWrap">
        <img id="coverPreview" src="{{ $room->cover_image_url ?? '' }}" alt="Cover preview" class="rounded border" style="width: 160px; height: 100px; object-fit: cover;">
    </div>
    <input id="cover_image" type="file" name="cover_image" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror" {{ isset($room) ? '' : 'required' }}>
    <div class="form-text">JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF. Max 4 MB.@if (isset($room)) Leave empty to keep the current cover.@endif</div>
    @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@isset($room)
    @if ($room->images->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Current Gallery</label>
            <div class="row row-cols-3 row-cols-sm-4 g-2">
                @foreach ($room->images as $image)
                    <div class="col">
                        <div class="position-relative">
                            <img src="{{ $image->image_url }}" alt="" class="rounded border w-100" style="aspect-ratio: 1 / 1; object-fit: cover;">
                            <label class="position-absolute top-0 end-0 m-1 bg-white rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; cursor: pointer;" title="Remove this photo">
                                <input type="checkbox" name="remove_gallery_images[]" value="{{ $image->id }}" class="form-check-input m-0" style="width: 14px; height: 14px;">
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-text">Check a photo to remove it when you save.</div>
        </div>
    @endif
@endisset

<div class="mb-4">
    <label for="gallery_images" class="form-label">{{ isset($room) ? 'Add Gallery Images' : 'Gallery Images' }}</label>
    <input id="gallery_images" type="file" name="gallery_images[]" accept="image/*" multiple class="form-control @error('gallery_images') is-invalid @enderror">
    <div class="form-text">Up to 10 photos. JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF, max 4 MB each.</div>
    @error('gallery_images')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @error('gallery_images.*')<div class="text-danger small fw-medium mt-1">{{ $message }}</div>@enderror
    <div id="galleryPreview" class="row row-cols-3 row-cols-sm-4 g-2 mt-1"></div>
</div>

<div class="d-flex gap-2">
    <a href="{{ isset($room) ? route('partner.resorts.rooms.show', [$resort, $room]) : route('partner.resorts.rooms.index', $resort) }}" class="btn btn-light">Cancel</a>
    <button type="submit" id="roomSubmitBtn" class="btn btn-primary flex-fill">{{ isset($room) ? 'Save Changes' : 'Save Room' }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Cover image preview.
        const coverInput = document.getElementById('cover_image');
        const coverPreview = document.getElementById('coverPreview');
        const coverPreviewWrap = document.getElementById('coverPreviewWrap');

        coverInput?.addEventListener('change', () => {
            const file = coverInput.files[0];
            if (!file) return;

            coverPreview.src = URL.createObjectURL(file);
            coverPreviewWrap.classList.remove('d-none');
        });

        // Gallery image previews.
        const galleryInput = document.getElementById('gallery_images');
        const galleryPreview = document.getElementById('galleryPreview');

        galleryInput?.addEventListener('change', () => {
            galleryPreview.innerHTML = '';

            Array.from(galleryInput.files).forEach((file) => {
                const col = document.createElement('div');
                col.className = 'col';

                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'rounded border w-100';
                img.style.aspectRatio = '1 / 1';
                img.style.objectFit = 'cover';

                col.appendChild(img);
                galleryPreview.appendChild(col);
            });
        });

        // Submit loading state.
        const form = document.getElementById('roomForm');
        const submitBtn = document.getElementById('roomSubmitBtn');

        form?.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving…';
        });
    });
</script>
