@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Customer Map</h4>
                <small class="text-muted">Visual lokasi pemasangan pelanggan + filter radius.</small>
            </div>
            <a href="{{ route('customers.index') }}" class="btn btn-light">Back to List</a>
        </div>

        <div class="row g-3">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <form id="filterForm" class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="q" placeholder="Nama/Email/No/ONT/Tech">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Site</label>
                                <select class="form-select" name="site_id">
                                    <option value="">-- All Sites --</option>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} @if($s->code) ({{ $s->code }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Plan</label>
                                <select class="form-select" name="plan_id">
                                    <option value="">-- All Plans --</option>
                                    @foreach($plans as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->bandwidth_mbps }} Mbps)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Any</option>
                                    <option value="active">Active</option>
                                    <option value="suspend">Suspend</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Harga Min</label>
                                <input type="number" class="form-control" name="price_min" placeholder="Rp">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Harga Max</label>
                                <input type="number" class="form-control" name="price_max" placeholder="Rp">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Radius (km)</label>
                                <input type="number" class="form-control" name="radius_km" min="0" step="0.1"
                                    placeholder="e.g. 2">
                            </div>

                            <div class="col-6">
                                <label class="form-label">Center Lat</label>
                                <input type="number" class="form-control" name="center_lat" step="0.0000001"
                                    value="{{ $defaultLat }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Center Lng</label>
                                <input type="number" class="form-control" name="center_lng" step="0.0000001"
                                    value="{{ $defaultLng }}">
                            </div>

                            <div class="col-12 d-grid gap-2">
                                <button type="button" id="btnUseMapCenter" class="btn btn-outline-secondary btn-sm">Use Map
                                    Center</button>
                                <button type="button" id="btnUseMyLocation" class="btn btn-outline-secondary btn-sm">Use My
                                    Location</button>
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <button type="button" id="btnReset" class="btn btn-light">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div><strong>Total markers:</strong> <span id="countMarkers">0</span></div>
                        <div><strong>Visible in map:</strong> <span id="countVisible">0</span></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div id="map" style="height: calc(100vh - 150px); min-height: 520px;" class="rounded border"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        const map = L.map('map').setView([{{ $defaultLat }}, {{ $defaultLng }}], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const cluster = L.markerClusterGroup();
        map.addLayer(cluster);

        let radiusCircle = null;
        const $form = document.getElementById('filterForm');
        const countMarkers = document.getElementById('countMarkers');
        const countVisible = document.getElementById('countVisible');

        function markerColor(status) {
            switch (status) {
                case 'active': return 'green';
                case 'suspend': return 'orange';
                case 'terminated': return 'red';
                default: return 'blue';
            }
        }

        function createIcon(color) {
            // simple colored circle marker (SVG)
            return L.divIcon({
                html: `<svg width="18" height="18" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="8" fill="${color}" stroke="white" stroke-width="2"/>
                </svg>`,
                className: '',
                iconSize: [18, 18],
                iconAnchor: [9, 9]
            });
        }

        function buildQuery() {
            const fd = new FormData($form);
            const params = new URLSearchParams();
            for (const [k, v] of fd.entries()) {
                if (v !== '') params.append(k, v);
            }
            return params.toString();
        }

        async function loadData() {
            const q = buildQuery();
            const url = `{{ route('customers.map-data') }}?${q}`;
            const res = await fetch(url);
            const geo = await res.json();
            cluster.clearLayers();

            let markersAdded = 0;
            (geo.features || []).forEach(f => {
                const props = f.properties || {};
                const [lng, lat] = f.geometry.coordinates;
                const color = markerColor(props.service_status);
                const marker = L.marker([lat, lng], { icon: createIcon(color) });

                const price = props.price_inc_ppn ? new Intl.NumberFormat('id-ID').format(props.price_inc_ppn) : '-';
                const bw = props.bandwidth_mbps ? `${props.bandwidth_mbps} Mbps` : '-';
                const active = props.is_active ? 'Active' : 'Inactive';

                const popup = `
            <div style="min-width:240px">
              <div><strong>${props.name ?? '-'}</strong></div>
              <div class="text-muted">${props.customer_no ?? ''}</div>
              <hr class="my-1">
              <div><strong>Site:</strong> ${props.site ?? '-'} ${props.site_code ? '(' + props.site_code + ')' : ''}</div>
              <div><strong>Plan:</strong> ${props.plan ?? '-'} • ${bw} • Rp ${price}</div>
              <div><strong>Status:</strong> ${props.service_status ?? '-'} (${active})</div>
              <div><strong>ONT:</strong> ${props.ont_brand ?? '-'} ${props.ont_sn ? '• SN: ' + props.ont_sn : ''}</div>
              <div><strong>Installed:</strong> ${props.installed_at ?? '-'}</div>
              <div><strong>Tech:</strong> ${props.technician_name ?? '-'}</div>
              <div class="mt-2 d-flex gap-2">
                ${props.edit_url ? `<a class="btn btn-sm btn-outline-secondary" href="${props.edit_url}" target="_blank">Edit</a>` : ''}
                ${props.maps_url ? `<a class="btn btn-sm btn-primary" href="${props.maps_url}" target="_blank">Open Maps</a>` : ''}
              </div>
            </div>
          `;

                marker.bindPopup(popup);
                cluster.addLayer(marker);
                markersAdded++;
            });

            countMarkers.textContent = markersAdded;
            updateVisibleCount();
            drawRadius();
        }

        function drawRadius() {
            const fd = new FormData($form);
            const radiusKm = parseFloat(fd.get('radius_km') || '0');
            const lat = parseFloat(fd.get('center_lat') || '{{ $defaultLat }}');
            const lng = parseFloat(fd.get('center_lng') || '{{ $defaultLng }}');

            if (radiusCircle) {
                map.removeLayer(radiusCircle);
                radiusCircle = null;
            }
            if (!isNaN(radiusKm) && radiusKm > 0 && !isNaN(lat) && !isNaN(lng)) {
                radiusCircle = L.circle([lat, lng], {
                    radius: radiusKm * 1000,
                    color: '#0d6efd',
                    fillColor: '#0d6efd',
                    fillOpacity: 0.08,
                    weight: 1
                }).addTo(map);
            }
        }

        function updateVisibleCount() {
            // perkiraan jumlah marker yang berada dalam viewport (cluster mengelompokkan)
            let visible = 0;
            cluster.eachLayer(layer => {
                const latlng = layer.getLatLng();
                if (map.getBounds().contains(latlng)) visible++;
            });
            countVisible.textContent = visible;
        }

        map.on('moveend zoomend', updateVisibleCount);

        // Buttons
        document.getElementById('btnUseMapCenter').addEventListener('click', () => {
            const c = map.getCenter();
            $form.center_lat.value = c.lat.toFixed(7);
            $form.center_lng.value = c.lng.toFixed(7);
            drawRadius();
        });

        document.getElementById('btnUseMyLocation').addEventListener('click', () => {
            if (!navigator.geolocation) { alert('Geolocation not supported'); return; }
            navigator.geolocation.getCurrentPosition(pos => {
                const { latitude, longitude } = pos.coords;
                $form.center_lat.value = latitude.toFixed(7);
                $form.center_lng.value = longitude.toFixed(7);
                map.setView([latitude, longitude], 15);
                drawRadius();
            }, err => {
                alert('Tidak bisa mengambil lokasi: ' + err.message);
            });
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            $form.reset();
            $form.center_lat.value = '{{ $defaultLat }}';
            $form.center_lng.value = '{{ $defaultLng }}';
            cluster.clearLayers();
            if (radiusCircle) { map.removeLayer(radiusCircle); radiusCircle = null; }
            countMarkers.textContent = '0';
            countVisible.textContent = '0';
            map.setView([{{ $defaultLat }}, {{ $defaultLng }}], 12);
        });

        $form.addEventListener('submit', (e) => {
            e.preventDefault();
            loadData();
        });

        // initial load
        loadData();
    </script>
@endpush