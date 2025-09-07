<div class="btn-group btn-group-sm">
    <a href="{{ route('invoices.show', $i->id) }}" class="btn btn-outline-primary">View</a>
    @if($i->status !== 'paid')
        <a href="{{ route('pay.invoice', $i->id) }}" class="btn btn-outline-success">Pay</a>
        <form action="{{ route('invoices.send.whatsapp', $i->id) }}" method="post" onsubmit="return confirm('Kirim WA?')">
            @csrf<button class="btn btn-outline-success">WA</button>
        </form>
        <form action="{{ route('invoices.send.email', $i->id) }}" method="post" onsubmit="return confirm('Kirim Email?')">
            @csrf<button class="btn btn-outline-dark">Email</button>
        </form>
    @endif
</div>