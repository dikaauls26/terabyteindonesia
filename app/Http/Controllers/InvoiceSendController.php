<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\WablasService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Mail\InvoiceMail;


class InvoiceSendController extends Controller
{
    public function __construct(private WablasService $wa) {}


    public function whatsapp(Invoice $invoice)
    {
        $msg = "Halo {$invoice->customer->name},%0A" .
            "Tagihan Internet periode " . $invoice->period->format('F Y') . " sebesar Rp" . number_format($invoice->total, 0, ',', '.') . ".%0A" .
            "Bayar: " . route('pay.invoice', $invoice->id);
        $this->wa->sendMessage($invoice->customer->phone, $msg);
        return back()->with('ok', 'WhatsApp sent');
    }


    public function email(Invoice $invoice)
    {
        Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));
        return back()->with('ok', 'Email sent');
    }


    public function blast(Request $r)
    {
        // TODO: query invoices unpaid by filters, dispatch jobs
        return back()->with('ok', 'Blast scheduled');
    }
}
