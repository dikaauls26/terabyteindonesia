@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 960px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">New Customer</h4>
            <a href="{{ route('customers.index') }}" class="btn btn-light">Back</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-bold mb-1">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Customer No</label>
                            <input name="customer_no" class="form-control" value="{{ old('customer_no') }}" required
                                placeholder="e.g. CUST-0001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                placeholder="name@domain.com">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxx / +62">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Site</label>
                            <select name="site_id" class="form-select" required>
                                <option value="">-- Select Site --</option>
                                @foreach($sites as $s)
                                    <option value="{{ $s->id }}" @selected(old('site_id') == $s->id)>{{ $s->name }} @if($s->code)
                                    ({{ $s->code }}) @endif</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Plan</label>
                            <select name="plan_id" class="form-select" required>
                                <option value="">-- Select Plan --</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>
                                        {{ $p->name }} ({{ $p->bandwidth_mbps }} Mbps) • Rp
                                        {{ number_format($p->price_inc_ppn, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Catatan khusus (opsional)">{{ old('notes') }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                    checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="col-md-4">
                            <label class="form-label">ONT Brand</label>
                            <input name="ont_brand" class="form-control" value="{{ old('ont_brand') }}"
                                placeholder="ZTE / Huawei / Fiberhome">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ONT Serial Number</label>
                            <input name="ont_sn" class="form-control" value="{{ old('ont_sn') }}"
                                placeholder="SN / MAC / LOID">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Latitude</label>
                            <input name="latitude" step="0.0000001" type="number" class="form-control"
                                value="{{ old('latitude') }}" placeholder="-6.2">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Longitude</label>
                            <input name="longitude" step="0.0000001" type="number" class="form-control"
                                value="{{ old('longitude') }}" placeholder="106.8">
                        </div>

                        <div class="col-12">
                            <small class="text-muted">
                                Tips: Isi koordinat (lat, lng) lalu klik
                                <a href="#" id="gmapsLink" target="_blank" rel="noopener">Buka di Google Maps</a>.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Installed At</label>
                            <input type="date" name="installed_at" class="form-control"
                                value="{{ old('installed_at', $c->installed_at ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Technician</label>
                            <input name="technician_name" class="form-control"
                                value="{{ old('technician_name', $c->technician_name ?? '') }}" placeholder="Nama teknisi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Status</label>
                            <select name="service_status" class="form-select">
                                @php $st = old('service_status', $c->service_status ?? 'active'); @endphp
                                <option value="active" @selected($st == 'active')>active</option>
                                <option value="suspend" @selected($st == 'suspend')>suspend</option>
                                <option value="terminated" @selected($st == 'terminated')>terminated</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a class="btn btn-light" href="{{ route('customers.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const latInput = document.querySelector('input[name="latitude"]');
        const lngInput = document.querySelector('input[name="longitude"]');
        const link = document.getElementById('gmapsLink');
        function updateLink() {
            const lat = latInput.value;
            const lng = lngInput.value;
            if (lat && lng) { link.href = `https://maps.google.com/?q=${lat},${lng}`; }
            else { link.href = '#'; }
        }
        latInput?.addEventListener('input', updateLink);
        lngInput?.addEventListener('input', updateLink);
        updateLink();
    </script>
@endsection