{{-- resources/views/vouchers/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <h4 class="mb-3">Edit Voucher</h4>
  <form method="POST" action="{{ route('vouchers.update', $v) }}" class="row g-3">
    @csrf @method('PUT')
    <div class="col-md-6">
      <label class="form-label">Code</label>
      <input name="code" class="form-control" value="{{ old('code',$v->code) }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Profile</label>
      <input name="profile" class="form-control" value="{{ old('profile',$v->profile) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Durasi (menit)</label>
      <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes',$v->duration_minutes) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Kuota (MB)</label>
      <input type="number" name="quota_mb" class="form-control" value="{{ old('quota_mb',$v->quota_mb) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Harga (Rp)</label>
      <input type="number" name="price" class="form-control" value="{{ old('price',$v->price) }}" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Currency</label>
      <input name="currency" class="form-control" value="{{ old('currency',$v->currency) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Batch ID</label>
      <input name="batch_id" class="form-control" value="{{ old('batch_id',$v->batch_id) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Router</label>
      <input name="router" class="form-control" value="{{ old('router',$v->router) }}">
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        @foreach(['available','reserved','sold','redeemed','expired','disabled'] as $s)
          <option value="{{ $s }}" @selected(old('status',$v->status)==$s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Active?</label>
      <select name="is_active" class="form-select">
        <option value="1" @selected($v->is_active)>Active</option>
        <option value="0" @selected(!$v->is_active)>Disabled</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2">{{ old('notes',$v->notes) }}</textarea>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Update</button>
      <a href="{{ route('vouchers.index') }}" class="btn btn-light">Back</a>
    </div>
  </form>
</div>
@endsection
