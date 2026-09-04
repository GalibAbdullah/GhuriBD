@php
    $pickerLat = old('latitude', $latitude ?? '');
    $pickerLng = old('longitude', $longitude ?? '');
@endphp

<div class="mb-4">
    <label class="form-label">Pin Location on Map</label>
    <div id="mapPicker" style="height: 300px;" class="rounded border"></div>
    <div class="form-text">Click anywhere on the map (or drag the marker) to set the exact coordinates.</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var mapEl = document.getElementById('mapPicker');
            var latInput = document.getElementById('latitude');
            var lngInput = document.getElementById('longitude');
            if (!mapEl || !latInput || !lngInput) return;

            var hasInitial = latInput.value && lngInput.value;
            var initial = hasInitial
                ? [parseFloat(latInput.value), parseFloat(lngInput.value)]
                : [23.6850, 90.3563];

            var map = L.map(mapEl).setView(initial, hasInitial ? 14 : 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);

            var marker = L.marker(initial, { draggable: true }).addTo(map);

            function setCoords(latLng) {
                latInput.value = latLng.lat.toFixed(7);
                lngInput.value = latLng.lng.toFixed(7);
            }

            map.on('click', function (event) {
                marker.setLatLng(event.latlng);
                setCoords(event.latlng);
            });

            marker.on('dragend', function () {
                setCoords(marker.getLatLng());
            });
        });
    </script>

    <input type="hidden" id="latitude" name="latitude" value="{{ $pickerLat }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ $pickerLng }}">
</div>
