@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">Invoice {{ $invoice->invoice_no }}</h5>
            @php($map = ['unpaid' => 'warning', 'paid' => 'success', 'expired' => 'secondary', 'canceled' => 'dark'])
            <span
                class="badge bg-{{ $map[$invoice->status] ?? 'secondary' }} text-uppercase">{{ $invoice->status }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-light">Kembali</a>
            @if($invoice->status !== 'paid')
                <a href="{{ route('pay.invoice', $invoice->id) }}" class="btn btn-success">Bayar</a>
                <form action="{{ route('invoices.send.whatsapp', $invoice->id) }}" method="post">
                    @csrf <button class="btn btn-outline-success">Kirim WA</button>
                </form>
                <form action="{{ route('invoices.send.email', $invoice->id) }}" method="post">
                    @csrf <button class="btn btn-outline-dark">Kirim Email</button>
                </form>
            @endif
        </div>
    </div>


    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Pelanggan</h6>
                    <div class="fw-semibold">{{ $invoice->customer->name }}</div>
                    <div>{{ $invoice->customer->customer_no }}</div>
                    <div class="text-muted small">{{ $invoice->customer->email }} • {{ $invoice->customer->phone }}
                    </div>
                    <div class="text-muted small">Site: {{ $invoice->site->name }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="text-muted">Periode & Rincian</h6>
                    <div>Periode: <b>{{ $invoice->period->format('F Y') }}</b></div>
                    <div>Jatuh Tempo: <b>{{ $invoice->due_date->format('d M Y') }}</b></div>
                    <div>Bandwidth: <b>{{ $invoice->plan_snapshot_json['bandwidth_mbps'] ?? '-' }} Mbps</b></div>
                    <div>Harga Plan (inc PPN):
                        <b>Rp{{ number_format($invoice->plan_snapshot_json['price_inc_ppn'] ?? 0, 0, ',', '.') }}</b></div>
                </div>
            </div>
        </div>
    </div>


    <div class="card mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th style="width:100px" class="text-end">Qty</th>
                            <th style="width:180px" class="text-end">Harga</th>
                            <th style="width:180px" class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $it)
                            <tr>
                                <td>{{ $it->description }}</td>
                                <td class="text-end">{{ $it->qty }}</td>
                                <td class="text-end">Rp{{ number_format($it->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp{{ number_format($it->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @endsection