@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Dashboard</h4>
                <small class="text-muted">Ringkasan operasional & pemasangan.</small>
            </div>
            <a href="{{ route('customers.map') }}" class="btn btn-outline-primary">Buka Peta Lengkap</a>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Total Customers</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalCustomers) }}</div>
                        <div class="mt-1 small">
                            <span class="badge bg-success">Active {{ number_format($activeCount) }}</span>
                            <span class="badge bg-warning text-dark">Suspend {{ number_format($suspendCount) }}</span>
                            <span class="badge bg-secondary">Term {{ number_format($terminatedCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Sites / Plans</div>
                        <div class="fs-4 fw-bold">{{ $totalSites }} / {{ $totalPlans }}</div>
                        <div class="small text-muted">Lokasi & paket aktif di sistem</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Est. MRR</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($mrr, 0, ',', '.') }}</div>
                        <div class="small text-muted">ARPU: Rp {{ number_format($arpu, 0, ',', '.') }}/bulan</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-muted">Avg Bandwidth (Active)</div>
                        <div class="fs-4 fw-bold">{{ number_format($avgBandwidth, 1) }} Mbps</div>
                        <div class="small text-muted">Rata-rata paket pelanggan aktif</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MINI HEATMAP + STATUS PIE --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Sebaran Pelanggan (Mini Heatmap)</div>
                        <small class="text-muted">Bobot berdasarkan harga plan</small>
                    </div>
                    <div class="card-body p-2">
                        <div id="miniMap" style="height: 340px;" class="rounded"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Status Layanan</div>
                    <div class="card-body">
                        <canvas id="statusPie"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- INSTALLS LINE + PLAN BAR --}}
        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Pemasangan per Bulan (12 terakhir)</div>
                    <div class="card-body">
                        <canvas id="installsLine"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Top Plans</div>
                    <div class="card-body">
                        <canvas id="planBar"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- HOTSPOTS + TOP SITES --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold">Hotspots (Prospek Area)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Center (Lat, Lng)</th>
                                        <th>Count</th>
                                        <th>Avg Price</th>
                                        <th>Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hotRows as $i => $h)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ number_format($h['center_lat'], 6) }}, {{ number_format($h['center_lng'], 6) }}
                                            </td>
                                            <td>{{ $h['count'] }}</td>
                                            <td>Rp {{ number_format($h['avg_price'], 0, ',', '.') }}</td>
                                            <td><a class="btn btn-sm btn-outline-primary"
                                                    href="https://maps.google.com/?q={{ $h['center_lat'] }},{{ $h['center_lng'] }}"
                                                    target="_blank" rel="noopener">Maps</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Grid ~1km. Area dengan kepadatan tinggi = potensi upsell/penarikan
                            baru.</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold">Top Sites (by Customers)</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Site</th>
                                        <th>Customers</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topSites as $i => $s)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $s->name }}</td>
                                            <td>{{ $s->c }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No data.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Gunakan untuk prioritas maintenance atau promosi lokal.</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .card {
            border-radius: 14px;
        }

        .card-header {
            background: #fff;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat/dist/leaflet-heat.js"></script>
    <script>
        // ====== DATA dari Controller ======
        const labels = @json($labels);
        const installsData = @json($seriesInstalls);
        const planLabels = @json($planLabels);
        const planSeries = @json($planSeries);
        const statusLabels = @json($statusLabels);
        const statusSeries = @json($statusSeries);
        const heatPoints = @json($heatPoints);

        // ====== MINI HEATMAP ======
        const map = L.map('miniMap', { zoomControl: false }).setView([-6.2, 106.82], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18, attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const heatArray = heatPoints.map(p => [p.lat, p.lng, p.w]);
        const heat = L.heatLayer(heatArray, { radius: 18, blur: 22, maxZoom: 17 }).addTo(map);

        // Fit bounds jika ada data
        if (heatArray.length) {
            const latlngs = heatArray.map(h => [h[0], h[1]]);
            map.fitBounds(latlngs, { padding: [20, 20] });
        }

        // ====== CHARTS ======
        // Line installs
        new Chart(document.getElementById('installsLine'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Installs',
                    data: installsData,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Bar plans
        new Chart(document.getElementById('planBar'), {
            type: 'bar',
            data: { labels: planLabels, datasets: [{ label: 'Customers', data: planSeries }] },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });

        // Pie status
        new Chart(document.getElementById('statusPie'), {
            type: 'pie',
            data: { labels: statusLabels, datasets: [{ data: statusSeries }] },
            options: { responsive: true }
        });
    </script>
@endpush