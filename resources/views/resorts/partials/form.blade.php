@php
    $selectedAmenities = old('amenities', $resort->amenities ?? []);
@endphp

<h4 class="h6 fw-semibold mb-3">Basic Information</h4>

<div class="mb-3">
    <label for="name" class="form-label">Resort Name</label>
    <input id="name" type="text" name="name" value="{{ old('name', $resort->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Sea Pearl Resort" required autofocus>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Tell travelers what makes this resort special" required>{{ old('description', $resort->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Location</h4>

<div class="row g-3">
    <div class="col-sm-6">
        <label for="division" class="form-label">Division</label>
        <select id="division" name="division" class="form-select @error('division') is-invalid @enderror" data-districts="{{ json_encode($districtsByDivision) }}" required>
            <option value="">Select division</option>
            @foreach ($divisions as $division)
                <option value="{{ $division }}" @selected(old('division', $resort->division ?? '') === $division)>{{ $division }}</option>
            @endforeach
        </select>
        @error('division')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="district" class="form-label">District</label>
        <select id="district" name="district" class="form-select @error('district') is-invalid @enderror" required>
            <option value="">Select division first</option>
        </select>
        @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-4 mt-3">
    <label for="address" class="form-label">Address</label>
    <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Street address / area" required>{{ old('address', $resort->address ?? '') }}</textarea>
    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Contact</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <label for="contact_phone" class="form-label">Contact Phone</label>
        <input id="contact_phone" type="text" name="contact_phone" value="{{ old('contact_phone', $resort->contact_phone ?? '') }}" class="form-control @error('contact_phone') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="price_range" class="form-label">Price Range</label>
        <input id="price_range" type="text" name="price_range" value="{{ old('price_range', $resort->price_range ?? '') }}" class="form-control @error('price_range') is-invalid @enderror" placeholder="e.g. ৳3,000 - ৳8,000" required>
        @error('price_range')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
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
    <div class="mb-2 {{ isset($resort) ? '' : 'd-none' }}" id="coverPreviewWrap">
        <img id="coverPreview" src="{{ $resort->cover_image_url ?? '' }}" alt="Cover preview" class="rounded border" style="width: 160px; height: 100px; object-fit: cover;">
    </div>
    <input id="cover_image" type="file" name="cover_image" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror" {{ isset($resort) ? '' : 'required' }}>
    <div class="form-text">JPEG, PNG, or WEBP. Max 4 MB.@if (isset($resort)) Leave empty to keep the current cover.@endif</div>
    @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@isset($resort)
    @if ($resort->images->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Current Gallery</label>
            <div class="row row-cols-3 row-cols-sm-4 g-2">
                @foreach ($resort->images as $image)
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
    <label for="gallery_images" class="form-label">{{ isset($resort) ? 'Add Gallery Images' : 'Gallery Images' }}</label>
    <input id="gallery_images" type="file" name="gallery_images[]" accept="image/*" multiple class="form-control @error('gallery_images') is-invalid @enderror">
    <div class="form-text">Up to 10 photos. JPEG, PNG, or WEBP, max 4 MB each.</div>
    @error('gallery_images')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @error('gallery_images.*')<div class="text-danger small fw-medium mt-1">{{ $message }}</div>@enderror
    <div id="galleryPreview" class="row row-cols-3 row-cols-sm-4 g-2 mt-1"></div>
</div>

<div class="d-flex gap-2">
    <a href="{{ isset($resort) ? route('partner.resorts.show', $resort) : route('partner.resorts.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" id="resortSubmitBtn" class="btn btn-primary flex-fill">{{ isset($resort) ? 'Save Changes' : 'Save Resort' }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Cascading division -> district select.
        const divisionSelect = document.getElementById('division');
        const districtSelect = document.getElementById('district');

        if (divisionSelect && districtSelect) {
            const districtsByDivision = JSON.parse(divisionSelect.dataset.districts);
            const currentDistrict = @json(old('district', $resort->district ?? ''));

            const populateDistricts = (division, selected) => {
                const districts = districtsByDivision[division] || [];

                if (!districts.length) {
                    districtSelect.innerHTML = '<option value="">Select division first</option>';
                    return;
                }

                districtSelect.innerHTML = '<option value="">Select district</option>' +
                    districts.map((district) => `<option value="${district}"${district === selected ? ' selected' : ''}>${district}</option>`).join('');
            };

            if (divisionSelect.value) {
                populateDistricts(divisionSelect.value, currentDistrict);
            }

            divisionSelect.addEventListener('change', () => populateDistricts(divisionSelect.value, ''));
        }

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
        const form = document.getElementById('resortForm');
        const submitBtn = document.getElementById('resortSubmitBtn');

        form?.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving…';
        });
    });
</script>
