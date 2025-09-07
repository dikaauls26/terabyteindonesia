@php
    $inv = $invoice;
    $company = [
        'name' => config('app.name', 'CRM WiFi'),
        'address' => 'Jl. Contoh No. 123, Jakarta',
        'phone' => '+62-21-123456',
        'email' => 'billing@example.com',
    ];
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $inv->invoice_number }}</title>
    <style>
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .container {
            width: 800px;
            margin: 0 auto;
        }

        .row {
            display: flex;
        }

        .col {
            flex: 1;
        }

        .right {
            text-align: right;
        }

        .mt-1 {
            margin-top: 6px;
        }

        .mt-2 {
            margin-top: 12px;
        }

        .mt-3 {
            margin-top: 18px;
        }

        .mb-1 {
            margin-bottom: 6px;
        }

        .mb-2 {
            margin-bottom: 12px;
        }

        .mb-3 {
            margin-bottom: 18px;
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border-bottom: 1px solid #eee;
            padding: 8px;
        }

        .table th {
            background: #f7f7f7;
            text-align: left;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            background: #eee;
        }

        .muted {
            color: #666;
        }

        .total {
            font-weight: 700;
            font-size: 18px;
        }

        .hr {
            height: 1px;
            background: #eee;
            border: none;
            margin: 12px 0;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="row mb-2">
            <div class="col">
                <h2 style="margin:0 0 6px 0;">INVOICE</h2>
                <div class="muted">Invoice No: <strong>{{ $inv->invoice_number }}</strong></div>
                <div class="muted">Status: <span class="badge">{{ strtoupper($inv->status) }}</span></div>
            </div>
            <div class="col right">
                <div class="mb-1"><strong>{{ $company['name'] }}</strong></div>
                <div class="muted">{{ $company['address'] }}</div>
                <div class="muted">{{ $company['phone'] }} • {{ $company['email'] }}</div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col card" style="margin-right:8px;">
                <div class="mb-1"><strong>Bill To</strong></div>
                <div>{{ $inv->customer_name }}</div>
                <div class="muted">{{ $inv->customer_email }} / {{ $inv->customer_phone }}</div>
                @if($inv->site_name)
                    <div class="mt-1"><strong>Site:</strong> {{ $inv->site_name }} @if($inv->site_code)
                    ({{ $inv->site_code }}) @endif</div>
                    <div class="muted">{{ $inv->site_address }}</div>
                @endif
            </div>
            <div class="col card">
                <div class="row">
                    <div class="col">
                        <div class="muted">Billing Month</div>
                        <div><strong>{{ $inv->billing_month }}</strong></div>
                    </div>
                    <div class="col">
                        <div class="muted">Billing Date</div>
                        <div><strong>{{ \Carbon\Carbon::parse($inv->billing_date)->format('d M Y') }}</strong></div>
                    </div>
                    <div class="col">
                        <div class="muted">Due Date</div>
                        <div>
                            <strong>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="table mb-2">
            <thead>
                <tr>
                    <th style="width:50%;">Description</th>
                    <th>Bandwidth</th>
                    <th class="right">Price (Inc. PPN)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $inv->plan_name ?? 'Internet Service' }}
                        <div class="muted">Periode: {{ $inv->billing_month }} • Invoice No: {{ $inv->invoice_number }}
                        </div>
                    </td>
                    <td>{{ $inv->bandwidth_mbps ? $inv->bandwidth_mbps . ' Mbps' : '-' }}</td>
                    <td class="right">Rp {{ number_format($inv->price_inc_ppn, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="mb-1"><strong>Notes</strong></div>
                    <div class="muted">
                        Pembayaran dapat dilakukan sebelum tanggal jatuh tempo. Layanan akan otomatis aktif/berlanjut
                        setelah pembayaran diterima.
                        Jika sudah melakukan pembayaran, abaikan pemberitahuan ini.
                    </div>
                    <div class="mt-1">
                        Metode pembayaran: Transfer Bank BCA 123456789 a.n. PT Contoh; atau via payment link (akan
                        dikirim via email/WA).
                    </div>
                </div>
            </div>
            <div class="col">
                <table class="table">
                    <tr>
                        <td class="right"><strong>Subtotal</strong></td>
                        <td class="right">Rp {{ number_format($inv->price_inc_ppn, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="right"><strong>Total</strong></td>
                        <td class="right total">Rp {{ number_format($inv->price_inc_ppn, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <hr class="hr">

        <div class="muted" style="font-size:12px;">
            Dokumen ini dibuat secara elektronik pada {{ now()->format('d M Y H:i') }}.
            Untuk bantuan, hubungi {{ $company['email'] }}.
        </div>
    </div>
</body>

</html>