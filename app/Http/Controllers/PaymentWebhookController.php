<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Invoice, Payment};
use Illuminate\Support\Facades\Log;


class PaymentWebhookController extends Controller
{
    public function handle(Request $r)
    {
        $payload = $r->all();
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['transaction_status'] ?? null;
        if (!$orderId || !$status) return response()->json(['ok' => false], 400);


        $invoice = Invoice::where('invoice_no', $orderId)->first();
        if (!$invoice) return response()->json(['ok' => false], 404);


        if (in_array($status, ['settlement', 'capture'])) {
            $invoice->update(['status' => 'paid']);
            Payment::create([
                'invoice_id' => $invoice->id,
                'provider' => 'midtrans',
                'provider_ref' => $payload['transaction_id'] ?? $orderId,
                'amount' => $invoice->total,
                'paid_at' => now(),
                'raw_webhook_json' => json_encode($payload)
            ]);
        } elseif ($status === 'expire') {
            $invoice->update(['status' => 'expired']);
        } elseif (in_array($status, ['cancel', 'deny'])) {
            $invoice->update(['status' => 'canceled']);
        }
        Log::info('midtrans_webhook', $payload);
        return response()->json(['ok' => true]);
    }
}
