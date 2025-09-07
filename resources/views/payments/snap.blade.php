<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
  window.snap.pay("{{ $snapToken }}", {
    onSuccess: function (result) { window.location.href = "{{ route('invoices.show', $invoice->id) }}"; },
    onPending: function (result) { console.log('pending', result); },
    onError: function (result) { alert('Payment failed'); },
  });
</script>