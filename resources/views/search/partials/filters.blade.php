@php
    $selectedAmenities = $filters['amenities'] ?? [];
@endphp

<div class="accordion" id="filterAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#filterDestination">Destination</button>
        </h2>
        <div id="filterDestination" class="accordion-collapse collapse show" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <input type="text" name="destination" value="{{ $filters['destination'] ?? '' }}" class="form-control" placeholder="e.g. Cox's Bazar">
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterLocation">Division &amp; District</button>
        </h2>
        <div id="filterLocation" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="mb-3">
                    <label for="filterDivision" class="form-label small">Division</label>
                    <select id="filterDivision" name="division" class="form-select" data-districts="{{ json_encode($districtsByDivision) }}">
                        <option value="">Any division</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division }}" @selected(($filters['division'] ?? '') === $division)>{{ $division }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filterDistrict" class="form-label small">District</label>
                    <select id="filterDistrict" name="district" class="form-select">
                        <option value="">Any district</option>
                        @if ($filters['division'] ?? null)
                            @foreach ($districtsByDivision[$filters['division']] ?? [] as $district)
                                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterPrice">Price Range</button>
        </h2>
        <div id="filterPrice" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">Min (৳)</label>
                        <input type="number" min="0" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="form-control" placeholder="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Max (৳)</label>
                        <input type="number" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="form-control" placeholder="Any">
                    </div>
                </div>
                <div class="form-text">For resorts, matches any room in that price range.</div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterAmenities">Amenities <span class="badge text-bg-light border ms-2">Resorts</span></button>
        </h2>
        <div id="filterAmenities" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                @foreach ($amenities as $index => $amenity)
                    <div class="form-check">
                        <input type="checkbox" name="amenities[]" value="{{ $amenity }}" id="filterAmenity_{{ $index }}" class="form-check-input" @checked(in_array($amenity, $selectedAmenities, true))>
                        <label for="filterAmenity_{{ $index }}" class="form-check-label small">{{ $amenity }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterDuration">Duration <span class="badge text-bg-light border ms-2">Tours</span></button>
        </h2>
        <div id="filterDuration" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                @foreach (['1-3' => '1 – 3 days', '4-7' => '4 – 7 days', '8+' => '8+ days'] as $value => $label)
                    <div class="form-check">
                        <input type="radio" name="duration" value="{{ $value }}" id="filterDuration_{{ $value }}" class="form-check-input" @checked(($filters['duration'] ?? '') === $value)>
                        <label for="filterDuration_{{ $value }}" class="form-check-label small">{{ $label }}</label>
                    </div>
                @endforeach
                <div class="form-check">
                    <input type="radio" name="duration" value="" id="filterDuration_any" class="form-check-input" @checked(empty($filters['duration']))>
                    <label for="filterDuration_any" class="form-check-label small">Any duration</label>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filterTravelers">Maximum Travelers <span class="badge text-bg-light border ms-2">Tours</span></button>
        </h2>
        <div id="filterTravelers" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
            <div class="accordion-body">
                <input type="number" min="1" name="max_travelers" value="{{ $filters['max_travelers'] ?? '' }}" class="form-control" placeholder="e.g. 4">
                <div class="form-text">Only show packages that fit at least this many travelers.</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary flex-fill">Apply Filters</button>
    <a href="{{ route('search.results', array_filter(['q' => $filters['q'] ?? null, 'tab' => $filters['tab'] ?? null])) }}" class="btn btn-light">Reset Filters</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#filterDivision').forEach((divisionSelect) => {
            const districtSelect = divisionSelect.closest('.accordion-body').querySelector('#filterDistrict');
            if (!districtSelect) return;

            const districtsByDivision = JSON.parse(divisionSelect.dataset.districts);

            divisionSelect.addEventListener('change', () => {
                const districts = districtsByDivision[divisionSelect.value] || [];
                districtSelect.innerHTML = '<option value="">Any district</option>' +
                    districts.map((district) => `<option value="${district}">${district}</option>`).join('');
            });
        });
    });
</script>
