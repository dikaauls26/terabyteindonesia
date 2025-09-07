@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">No Pelanggan</label>
        <input type="text" name="customer_no" value="{{ old('customer_no', $c->customer_no ?? '') }}"
            class="form-control" required>
        @error('customer_no')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Nama</label>
        <input type="text" name="name" value="{{ old('name', $c->name ?? '') }}" class="form-control" required>
        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $c->phone ?? '') }}" class="form-control" required>
        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $c->email ?? '') }}" class="form-control" required>
        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Site</label>
        <select name="site_id" class="form-select" required>
            <option value="">- Pilih -</option>
            @foreach($sites as $s)
                <option value="{{ $s->id }}" @selected(old('site_id', $c->site_id ?? '') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        @error('site_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Plan</label>
        <select id="plan_id" name="plan_id" class="form-select" required>
            <option value="">- Pilih -</option>
            @foreach($plans as $p)
                <option value="{{ $p->id }}" data-bandwidth="{{ $p->bandwidth_mbps }}" data-price="{{ $p->price_inc_ppn }}"
                    @selected(old('plan_id', $c->plan_id ?? '') == $p->id)>
                    {{ $p->name }} ({{ $p->bandwidth_mbps }} Mbps)
                </option>
            @endforeach
        </select>
        <div id="plan-help" class="form-text"></div>
        @error('plan_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-12">
        <label class="form-label">Catatan</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $c->notes ?? '') }}</textarea>
    </div>
    <div class="col-md-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                @checked(old('is_active', $c->is_active ?? true))>
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>
<hr>
<div class="d-flex gap-2">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('customers.index') }}" class="btn btn-light">Batal</a>
</div>


@push('scripts')
    <script>
        function fmtRupiah(num) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
        }
@endpush