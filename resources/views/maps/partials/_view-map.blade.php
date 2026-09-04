@if ($model->hasCoordinates())
    <div id="locationMap-{{ $model->id }}" class="rounded-3 overflow-hidden border mb-2" style="height: 220px;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('locationMap-{{ $model->id }}');
            if (!el) return;

            var position = [{{ $model->latitude }}, {{ $model->longitude }}];
            var map = L.map(el).setView(position, 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);

            L.marker(position).addTo(map).bindPopup({{ Illuminate\Support\Js::from($title) }});
        });
    </script>
    <a href="{{ $model->googleMapsUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm w-100">Open in Google Maps</a>
@else
    <div class="text-secondary small">Location not pinned on the map yet.</div>
@endif
