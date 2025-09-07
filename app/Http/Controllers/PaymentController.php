<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;


class PaymentController extends Controller
{
    public function redirectToSnap(Invoice $invoice)
    {
        MidtransConfig::$serverKey = env('MIDTRANS_SERVER_KEY');
        MidtransConfig::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;


        $params = [
            'transaction_details' => ['order_id' => $invoice->invoice_no, 'gross_amount' => (int)$invoice->total],
            'customer_details' => [
                'first_name' => $invoice->customer->name,
                'email' => $invoice->customer->email,
                'phone' => $invoice->customer->phone,
            ],
            'item_details' => [['id' => 'plan-' . $invoice->id, 'price' => (int)$invoice->total, 'quantity' => 1, 'name' => 'Internet ' . $invoice->period->format('F Y')]]
        ];


        $snapToken = Snap::getToken($params);
        $invoice->update(['snap_token' => $snapToken]);
        return view('payments.snap', compact('invoice', 'snapToken'));
    }
}
