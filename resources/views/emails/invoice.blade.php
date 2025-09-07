<p>Halo {{ $invoice->customer->name }},</p>
<p>Berikut tagihan internet periode <b>{{ $invoice->period->format('F Y') }}</b> sebesar
    <b>Rp{{ number_format($invoice->total, 0, ',', '.') }}</b>.</p>
<p><a href="{{ route('pay.invoice', $invoice->id) }}" class="btn btn-primary">Bayar Sekarang</a></p>