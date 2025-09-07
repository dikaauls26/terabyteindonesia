{{-- resources/views/vouchers/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Hotspot Vouchers</h4>
      <small class="text-muted">Kelola kode voucher Mikrotik untuk penjualan via portal.</small>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">Tambah Voucher</button>
      <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">Import Batch</button>
    </div>
  </div>

  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-2" method="GET" action="{{ route('vouchers.index') }}">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            <option value="">All</option>
            @foreach(['available','reserved','sold','redeemed','expired','disabled'] as $s)
              <option value="{{ $s }}" @selected(($status ?? '')==$s)>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Search</label>
          <input class="form-control" name="q" value="{{ $q }}" placeholder="Cari kode / profile / email / phone / batch / router">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-secondary me-2">Filter</button>
          <a href="{{ route('vouchers.index') }}" class="btn btn-light">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Paket</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Batch/Router</th>
            <th>Buyer</th>
            <th style="width:180px;">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $i => $v)
          <tr>
            <td>{{ ($rows->firstItem() ?? 1) + $i }}</td>
            <td><code>{{ $v->code }}</code></td>
            <td>
              <div class="fw-semibold">{{ $v->profile ?? '-' }}</div>
              <small class="text-muted">
                {{ $v->duration_minutes ? $v->duration_minutes.' min' : '-' }}
                • {{ $v->quota_mb ? number_format($v->quota_mb).' MB' : 'unlimited' }}
              </small>
            </td>
            <td>Rp {{ number_format($v->price,0,',','.') }}</td>
            <td>
              @php $badge = [
                'available'=>'success','reserved'=>'warning','sold'=>'primary',
                'redeemed'=>'secondary','expired'=>'dark','disabled'=>'secondary'
              ][$v->status] ?? 'secondary'; @endphp
              <span class="badge bg-{{ $badge }}">{{ strtoupper($v->status) }}</span>
              @if(!$v->is_active) <span class="badge bg-secondary">Disabled</span> @endif
            </td>
            <td>
              <div>{{ $v->batch_id ?? '-' }}</div>
              <small class="text-muted">{{ $v->router ?? '' }}</small>
            </td>
            <td>
              @if($v->buyer_email || $v->buyer_phone)
                <div>{{ $v->buyer_name ?? '-' }}</div>
                <small class="text-muted">{{ $v->buyer_email }} / {{ $v->buyer_phone }}</small>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <a href="{{ route('vouchers.edit', $v) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
              <form action="{{ route('vouchers.destroy', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete voucher?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $rows->links() }}
    </div>
  </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="addVoucherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('vouchers.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Tambah Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Code</label>
            <input name="code" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Profile</label>
            <input name="profile" class="form-control" placeholder="contoh: 2H-5GB">
          </div>
          <div class="col-md-4">
            <label class="form-label">Durasi (menit)</label>
            <input type="number" name="duration_minutes" class="form-control" min="0" placeholder="mis. 120">
          </div>
          <div class="col-md-4">
            <label class="form-label">Kuota (MB)</label>
            <input type="number" name="quota_mb" class="form-control" min="0" placeholder="mis. 2048">
          </div>
          <div class="col-md-4">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="price" class="form-control" min="0" value="0" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Batch ID</label>
            <input name="batch_id" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Router</label>
            <input name="router" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Aktif?</label>
            <select name="is_active" class="form-select">
              <option value="1">Active</option>
              <option value="0">Disabled</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Catatan</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div></div>
</div>

{{-- Modal Import --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('vouchers.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Import Batch Voucher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-light border">
          Format per baris: <code>CODE</code> atau <code>CODE|PROFILE|DURATION_MIN|QUOTA_MB|PRICE</code><br>
          Contoh:<br>
          <code>ABC123|2H-5GB|120|5120|10000</code><br>
          <code>DEF456</code>
        </div>
        <div class="mb-3">
          <label class="form-label">Daftar Kode (satu per baris)</label>
          <textarea name="bulk_codes" class="form-control" rows="10" required></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Batch ID</label>
            <input name="batch_id" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Router</label>
            <input name="router" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Aktif?</label>
            <select name="is_active" class="form-select">
              <option value="1">Active</option>
              <option value="0">Disabled</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Import</button>
      </div>
    </form>
  </div></div>
</div>
@endsection
