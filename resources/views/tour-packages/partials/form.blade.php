@php
    $selectedIncluded = old('included_services', $tourPackage->included_services ?? []);
    $selectedExcluded = old('excluded_services', $tourPackage->excluded_services ?? []);
@endphp

<h4 class="h6 fw-semibold mb-3">Basic Information</h4>

<div class="mb-3">
    <label for="title" class="form-label">Package Title</label>
    <input id="title" type="text" name="title" value="{{ old('title', $tourPackage->title ?? '') }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Sundarbans Wildlife Safari" required autofocus>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="destination" class="form-label">Destination</label>
    <input id="destination" type="text" name="destination" value="{{ old('destination', $tourPackage->destination ?? '') }}" class="form-control @error('destination') is-invalid @enderror" placeholder="e.g. Sundarbans" required>
    @error('destination')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-sm-6">
        <label for="division" class="form-label">Division</label>
        <select id="division" name="division" class="form-select @error('division') is-invalid @enderror" data-districts="{{ json_encode($districtsByDivision) }}" required>
            <option value="">Select division</option>
            @foreach ($divisions as $division)
                <option value="{{ $division }}" @selected(old('division', $tourPackage->division ?? '') === $division)>{{ $division }}</option>
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
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Tell travelers what makes this tour special" required>{{ old('description', $tourPackage->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Duration &amp; Pricing</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <label for="duration_days" class="form-label">Days</label>
        <input id="duration_days" type="number" min="1" name="duration_days" value="{{ old('duration_days', $tourPackage->duration_days ?? '') }}" class="form-control @error('duration_days') is-invalid @enderror" required>
        @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-3">
        <label for="duration_nights" class="form-label">Nights</label>
        <input id="duration_nights" type="number" min="0" name="duration_nights" value="{{ old('duration_nights', $tourPackage->duration_nights ?? '') }}" class="form-control @error('duration_nights') is-invalid @enderror" required>
        @error('duration_nights')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-3">
        <label for="price" class="form-label">Price (৳)</label>
        <input id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', $tourPackage->price ?? '') }}" class="form-control @error('price') is-invalid @enderror" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-3">
        <label for="max_travelers" class="form-label">Max Travelers</label>
        <input id="max_travelers" type="number" min="1" name="max_travelers" value="{{ old('max_travelers', $tourPackage->max_travelers ?? '') }}" class="form-control @error('max_travelers') is-invalid @enderror" required>
        @error('max_travelers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Meeting Information</h4>

<div class="row g-3 mb-4">
    <div class="col-sm-6">
        <label for="meeting_point" class="form-label">Meeting Point</label>
        <input id="meeting_point" type="text" name="meeting_point" value="{{ old('meeting_point', $tourPackage->meeting_point ?? '') }}" class="form-control @error('meeting_point') is-invalid @enderror" placeholder="e.g. Khulna Bus Terminal" required>
        @error('meeting_point')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label for="start_location" class="form-label">Start Location</label>
        <input id="start_location" type="text" name="start_location" value="{{ old('start_location', $tourPackage->start_location ?? '') }}" class="form-control @error('start_location') is-invalid @enderror" placeholder="e.g. Khulna" required>
        @error('start_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Itinerary</h4>

<div class="mb-4">
    <label for="itinerary" class="form-label">Day-by-day Itinerary</label>
    <textarea id="itinerary" name="itinerary" rows="8" class="form-control @error('itinerary') is-invalid @enderror" placeholder="Day 1: ...&#10;Day 2: ..." required>{{ old('itinerary', $tourPackage->itinerary ?? '') }}</textarea>
    @error('itinerary')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Included Services</h4>

<div class="row row-cols-2 row-cols-sm-3 g-2 mb-4">
    @foreach ($services as $index => $service)
        <div class="col">
            <div class="form-check">
                <input type="checkbox" name="included_services[]" value="{{ $service }}" id="included_{{ $index }}" class="form-check-input" @checked(in_array($service, $selectedIncluded, true))>
                <label for="included_{{ $index }}" class="form-check-label small">{{ $service }}</label>
            </div>
        </div>
    @endforeach
</div>
@error('included_services')<div class="text-danger small fw-medium mb-4">{{ $message }}</div>@enderror

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Excluded Services</h4>

<div class="row row-cols-2 row-cols-sm-3 g-2 mb-4">
    @foreach ($services as $index => $service)
        <div class="col">
            <div class="form-check">
                <input type="checkbox" name="excluded_services[]" value="{{ $service }}" id="excluded_{{ $index }}" class="form-check-input" @checked(in_array($service, $selectedExcluded, true))>
                <label for="excluded_{{ $index }}" class="form-check-label small">{{ $service }}</label>
            </div>
        </div>
    @endforeach
</div>
@error('excluded_services')<div class="text-danger small fw-medium mb-4">{{ $message }}</div>@enderror

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Status</h4>

<div class="mb-4" style="max-width: 240px;">
    <label for="status" class="form-label">Status</label>
    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
        @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected(old('status', $tourPackage->status ?? 'Active') === $status)>{{ $status }}</option>
        @endforeach
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-4">

<h4 class="h6 fw-semibold mb-3">Images</h4>

<div class="mb-3">
    <label for="cover_image" class="form-label">Cover Image</label>
    <div class="mb-2 {{ isset($tourPackage) ? '' : 'd-none' }}" id="coverPreviewWrap">
        <img id="coverPreview" src="{{ $tourPackage->cover_image_url ?? '' }}" alt="Cover preview" class="rounded border" style="width: 160px; height: 100px; object-fit: cover;">
    </div>
    <input id="cover_image" type="file" name="cover_image" accept="image/*" class="form-control @error('cover_image') is-invalid @enderror" {{ isset($tourPackage) ? '' : 'required' }}>
    <div class="form-text">JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF. Max 4 MB.@if (isset($tourPackage)) Leave empty to keep the current cover.@endif</div>
    @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@isset($tourPackage)
    @if ($tourPackage->images->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Current Gallery</label>
            <div class="row row-cols-3 row-cols-sm-4 g-2">
                @foreach ($tourPackage->images as $image)
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
    <label for="gallery_images" class="form-label">{{ isset($tourPackage) ? 'Add Gallery Images' : 'Gallery Images' }}</label>
    <input id="gallery_images" type="file" name="gallery_images[]" accept="image/*" multiple class="form-control @error('gallery_images') is-invalid @enderror">
    <div class="form-text">Up to 10 photos. JPEG, PNG, GIF, WebP, BMP, SVG, AVIF, HEIC, HEIF, or TIFF, max 4 MB each.</div>
    @error('gallery_images')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @error('gallery_images.*')<div class="text-danger small fw-medium mt-1">{{ $message }}</div>@enderror
    <div id="galleryPreview" class="row row-cols-3 row-cols-sm-4 g-2 mt-1"></div>
</div>

<div class="d-flex gap-2">
    <a href="{{ isset($tourPackage) ? route('partner.packages.show', $tourPackage) : route('partner.packages.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" id="packageSubmitBtn" class="btn btn-primary flex-fill">{{ isset($tourPackage) ? 'Save Changes' : 'Save Package' }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Cascading division -> district select.
        const divisionSelect = document.getElementById('division');
        const districtSelect = document.getElementById('district');

        if (divisionSelect && districtSelect) {
            const districtsByDivision = JSON.parse(divisionSelect.dataset.districts);
            const currentDistrict = @json(old('district', $tourPackage->district ?? ''));

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
        const form = document.getElementById('packageForm');
        const submitBtn = document.getElementById('packageSubmitBtn');

        form?.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving…';
        });
    });
</script>
