@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Customers</h4>
                <small class="text-muted">Kelola pelanggan & keterkaitannya dengan Site dan Plan.</small>
            </div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">+ New Customer</a>
            <a href="{{ route('customers.map') }}" class="btn btn-danger">Customer Maps</a>
        </div>

        @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Filter/Search -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="q" class="form-control" value="{{ old('q', $q ?? '') }}"
                            placeholder="Cari: Customer No / Name / Email / Phone">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Filter Site</label>
                        <select name="site_id" class="form-select">
                            <option value="">-- All Sites --</option>
                            @foreach($sites as $s)
                                <option value="{{ $s->id }}" @selected(($siteId ?? '') == $s->id)>
                                    {{ $s->name }} @if($s->code) ({{ $s->code }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Harga Min (Rp)</label>
                        <input type="number" min="0" name="price_min" class="form-control"
                            value="{{ old('price_min', $priceMin ?? '') }}"
                            placeholder="{{ number_format($priceMinAvail ?? 0, 0, ',', '.') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Harga Max (Rp)</label>
                        <input type="number" min="0" name="price_max" class="form-control"
                            value="{{ old('price_max', $priceMax ?? '') }}"
                            placeholder="{{ number_format($priceMaxAvail ?? 0, 0, ',', '.') }}">
                    </div>

                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>

                    <div class="col-12">
                        @php
                            $hasFilter = !empty($q) || !empty($siteId) || (!empty($priceMin) || !empty($priceMax));
                          @endphp
                        @if($hasFilter)
                            <div class="mt-2">
                                <span class="me-2">Active filters:</span>
                                @if(!empty($q))
                                    <span class="badge bg-secondary">q: "{{ $q }}"</span>
                                @endif
                                @if(!empty($siteId))
                                    @php $siteName = optional($sites->firstWhere('id', $siteId))->name; @endphp
                                    <span class="badge bg-secondary">site: {{ $siteName ?? $siteId }}</span>
                                @endif
                                @if(!empty($priceMin))
                                    <span class="badge bg-secondary">min: Rp {{ number_format($priceMin, 0, ',', '.') }}</span>
                                @endif
                                @if(!empty($priceMax))
                                    <span class="badge bg-secondary">max: Rp {{ number_format($priceMax, 0, ',', '.') }}</span>
                                @endif
                                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary ms-2">Reset</a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Customer No</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Site</th>
                            <th>Plan</th>
                            <th>Active</th>
                            <th>Notes</th>
                            <th style="width:140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $i => $c)
                            @php
                                $rowNumber = ($customers->firstItem() ?? 1) + $i;
                            @endphp
                            <tr>
                                <td>{{ $rowNumber }}</td>
                                <td><code>{{ $c->customer_no }}</code></td>
                                <td>{{ $c->name }}</td>
                                <td>
                                    <div>{{ $c->email }}</div>
                                    <div class="text-muted">{{ $c->phone }}</div>
                                </td>
                                <td>
                                    {{ $c->site->name ?? '-' }}
                                    @if(!empty($c->site?->code))
                                        <small class="text-muted d-block">{{ $c->site->code }}</small>
                                    @endif
                                    @if(!empty($c->site?->address))
                                        <small class="text-muted d-block text-truncate" style="max-width:220px;">
                                            {{ $c->site->address }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    {{ $c->plan->name ?? '-' }}
                                    @if(isset($c->plan))
                                        <small class="text-muted d-block">
                                            {{ $c->plan->bandwidth_mbps }} Mbps • Rp
                                            {{ number_format($c->plan->price_inc_ppn, 0, ',', '.') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($c->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width:280px;">{{ $c->notes }}</td>
                                <td>
                                    <a href="{{ route('customers.edit', $c) }}"
                                        class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('customers.destroy', $c) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada data dengan filter saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                {{ $customers->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection