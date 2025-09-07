@extends('layouts.app')

@section('content')
  <div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Invoices</h4>
      <small class="text-muted">Generate manual per-bulan, kirim via Email/WhatsApp, dan kelola status.</small>
    </div>
    </div>

    {{-- Flash --}}
    @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Filter (GET) & Generate All (POST) - NOT nested --}}
    <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3 align-items-end">
      {{-- FORM FILTER (GET) --}}
      <div class="col-md-3">
        <form method="GET" action="{{ route('invoices.index') }}">
        <label class="form-label">Bulan</label>
        <input type="month" name="month" class="form-control" value="{{ $month }}">
        <div class="d-grid mt-2">
          <button class="btn btn-outline-primary">Terapkan</button>
        </div>
        <small class="text-muted d-block mt-2">Menampilkan invoice pada bulan yang dipilih.</small>
        </form>
      </div>

      {{-- INFO & FORM GENERATE ALL (POST) --}}
      <div class="col-md-9">
        <div
        class="alert alert-light border d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mb-2 mb-md-0">
          <strong>Generate All</strong> — buat invoice untuk <u>semua customer aktif</u> pada bulan terpilih.
          <div class="small text-muted">Idempotent: tidak membuat duplikat untuk customer yang sudah punya invoice
          pada bulan tersebut.</div>
        </div>
        <form method="POST" action="{{ route('invoices.generate-all') }}" class="d-flex gap-2">
          @csrf
          <input type="date" name="billing_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"
          required>
          <input type="number" min="0" max="90" name="due_in_days" class="form-control form-control-sm"
          placeholder="Jatuh tempo (hari)">
          <button class="btn btn-sm btn-primary" type="submit"
          onclick="return confirm('Generate semua invoice untuk bulan ini?')">Generate All</button>
        </form>
        </div>
      </div>
      </div>

      {{-- Toolbar Blast --}}
      <div class="d-flex justify-content-between align-items-center mt-2">
      <div>
        <span class="text-muted">Periode:</span>
        <strong>{{ $month }}</strong>
        <span class="text-muted ms-2">Total data:</span>
        <strong>{{ number_format($invoices->count()) }}</strong>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="{{ route('invoices.blast.email') }}">
        @csrf
        <button class="btn btn-outline-primary btn-sm" {{ $invoices->count() ? '' : 'disabled' }}>
          Blast All Email ({{ $invoices->count() }})
        </button>
        </form>
        <form method="POST" action="{{ route('invoices.blast.whatsapp') }}">
        @csrf
        <button class="btn btn-outline-success btn-sm" {{ $invoices->count() ? '' : 'disabled' }}>
          Blast All WhatsApp ({{ $invoices->count() }})
        </button>
        </form>
      </div>
      </div>
    </div>
    </div>

    {{-- TABLE: DataTables client-side (JANGAN bungkus .table-responsive) --}}
    <div class="card">
    <div class="card-body">
      <table class="table table-striped table-hover align-middle w-100" id="invoicesTable">
      <thead>
        <tr>
        <th>#</th>
        <th>Invoice</th>
        <th>Customer</th>
        <th>Site</th>
        <th>Plan</th>
        <th class="text-end">Amount</th>
        <th>Billing</th>
        <th>Due</th>
        <th>Status</th>
        <th class="text-nowrap">Action</th>
        </tr>
      </thead>
      <tbody>
        @php $n = 1; @endphp
        @forelse($invoices as $inv)
        <tr>
        <td data-order="{{ $n }}">{{ $n++ }}</td>
        <td>
        <div class="fw-semibold">{{ $inv->invoice_number }}</div>
        <small class="text-muted">{{ $inv->billing_month }}</small>
        </td>
        <td>
        <div class="fw-semibold">{{ $inv->customer_name }}</div>
        <small class="text-muted">{{ $inv->customer_email }} / {{ $inv->customer_phone }}</small>
        </td>
        <td>
        {{ $inv->site_name ?? '-' }}
        @if($inv->site_code)
        <small class="text-muted d-block">{{ $inv->site_code }}</small>
      @endif
        </td>
        <td>
        {{ $inv->plan_name ?? '-' }}
        @if($inv->bandwidth_mbps)
        <small class="text-muted d-block">{{ $inv->bandwidth_mbps }} Mbps</small>
      @endif
        </td>
        <td class="text-end" data-order="{{ (int) $inv->price_inc_ppn }}">Rp
        {{ number_format($inv->price_inc_ppn, 0, ',', '.') }}</td>
        <td data-order="{{ \Carbon\Carbon::parse($inv->billing_date)->format('Ymd') }}">
        {{ \Carbon\Carbon::parse($inv->billing_date)->format('d M Y') }}
        </td>
        <td data-order="{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Ymd') : '99999999' }}">
        {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '-' }}
        </td>
        <td data-order="{{ $inv->status }}">
        @php
        $badge = ['unpaid' => 'secondary', 'paid' => 'success', 'void' => 'dark'][$inv->status] ?? 'secondary';
      @endphp
        <span class="badge bg-{{ $badge }}">{{ strtoupper($inv->status) }}</span>
        </td>
        <td class="text-nowrap">
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('invoices.show', $inv) }}">View</a>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.pdf', $inv) }}" target="_blank">PDF</a>

        <form method="POST" action="{{ route('invoices.send.email', $inv) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-primary">Email</button>
        </form>

        <form method="POST" action="{{ route('invoices.send.whatsapp', $inv) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success">WA</button>
        </form>

        <form method="POST" action="{{ route('invoices.destroy', $inv) }}" class="d-inline"
        onsubmit="return confirm('Delete this invoice?')">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-outline-danger">Del</button>
        </form>
        </td>
        </tr>
      @empty
      <tr>
      <td colspan="10" class="text-center text-muted py-4">Belum ada invoice pada bulan ini.</td>
      </tr>
      @endforelse
      </tbody>
      </table>
    </div>
    </div>
  </div>
@endsection

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.2/datatables.min.css">
  <style>
    /* Pastikan paginate & kolom action tidak jatuh ke baris bawah */
    div.dataTables_wrapper .dataTables_paginate {
    white-space: nowrap;
    }

    div.dataTables_wrapper div.row {
    align-items: center;
    }

    #invoicesTable td.text-nowrap,
    #invoicesTable th.text-nowrap {
    white-space: nowrap;
    }
  </style>
@endpush

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/r-3.0.2/datatables.min.js"></script>
  <script>
    $(function () {
    const dt = $('#invoicesTable').DataTable({
      responsive: true,
      autoWidth: false,
      scrollX: true,
      pageLength: 25, // default 100
      lengthMenu: [[25, 50, 100, 250, 500, -1], [25, 50, 100, 250, 500, 'All']],
      order: [[1, 'asc']], // kolom Invoice
      columnDefs: [
      { targets: [9], className: 'text-nowrap' },  // Action nowrap
      { targets: [5], className: 'text-end' },     // Amount right align
      { targets: [0, 9], orderable: false },        // # dan Action non-sort
      ],
      language: {
      search: "Cari:",
      lengthMenu: "Tampil _MENU_ baris",
      info: "Menampilkan _START_–_END_ dari _TOTAL_",
      infoEmpty: "Tidak ada data",
      zeroRecords: "Tidak ditemukan",
      paginate: { previous: "&laquo;", next: "&raquo;" }
      },
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
      "<'row'<'col-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      deferRender: true   // lebih ringan saat data besar
    });

    $(window).on('resize', () => dt.columns.adjust());
    dt.columns.adjust();
    });
  </script>
@endpush