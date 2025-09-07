<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // LIST per-bulan
    public function index(\Illuminate\Http\Request $r)
    {
        $month = $r->input('month') ?: date('Y-m');

        // AMBIL SEMUA DATA BULAN TERPILIH (bukan paginate)
        $invoices = \App\Models\Invoice::where('billing_month', $month)
            ->orderByDesc('billing_date')
            ->orderBy('invoice_number')
            ->get();

        return view('invoices.index', [
            'invoices' => $invoices,
            'month'    => $month,
        ]);
    }

    // GENERATE ALL untuk bulan tertentu (manual)
    public function generateAll(Request $r)
    {
        $data = $r->validate([
            'billing_date' => ['required', 'date'],
            'due_in_days'  => ['nullable', 'integer', 'min:0', 'max:90'],
        ]);

        $billingDate  = date('Y-m-d', strtotime($data['billing_date']));
        $billingMonth = date('Y-m', strtotime($billingDate));
        $dueDate      = isset($data['due_in_days'])
            ? date('Y-m-d', strtotime($billingDate . ' +' . $data['due_in_days'] . ' days'))
            : null;

        // customers aktif: service_status=active dan is_active=1
        $customers = Customer::with(['site', 'plan'])
            ->where('service_status', 'active')
            ->where('is_active', true)
            ->get();

        $created = 0;

        DB::transaction(function () use ($customers, $billingDate, $billingMonth, $dueDate, &$created) {
            foreach ($customers as $c) {
                // skip jika invoice customer untuk bulan tsb sudah ada
                $exists = Invoice::where('customer_id', $c->id)
                    ->where('billing_month', $billingMonth)
                    ->exists();
                if ($exists) continue;

                $plan = $c->plan; // bisa null, handle default
                $site = $c->site;

                // nomor invoice: INV-YYYYMM-<custId>-<seq harian>
                $seq   = Invoice::whereDate('billing_date', $billingDate)->lockForUpdate()->count() + 1;
                $invNo = sprintf('INV-%s-%04d-%04d', date('Ym', strtotime($billingDate)), $c->id, $seq);

                Invoice::create([
                    'invoice_number' => $invNo,
                    'customer_id'    => $c->id,
                    'site_id'        => $site->id ?? null,
                    'plan_id'        => $plan->id ?? null,

                    // snapshot
                    'customer_name'  => $c->name,
                    'customer_email' => $c->email,
                    'customer_phone' => $c->phone,
                    'site_name'      => $site->name ?? null,
                    'site_code'      => $site->code ?? null,
                    'site_address'   => $site->address ?? null,
                    'plan_name'      => $plan->name ?? null,
                    'bandwidth_mbps' => $plan->bandwidth_mbps ?? null,
                    'price_inc_ppn'  => (int)($plan->price_inc_ppn ?? 0),

                    'billing_date'   => $billingDate,
                    'billing_month'  => $billingMonth,
                    'due_date'       => $dueDate,
                    'status'         => 'unpaid',
                    'notes'          => null,
                ]);

                $created++;
            }
        });

        return redirect()->route('invoices.index', ['month' => $billingMonth])
            ->with('ok', "Generate selesai. Dibuat: {$created} invoice untuk bulan {$billingMonth}.");
    }

    // DETAIL sederhana
    public function show(Invoice $invoice)
    {
        return view('invoices.show', compact('invoice'));
    }

    // PDF/Print
    public function pdf(Invoice $invoice)
    {
        // Render Blade sebagai HTML print-ready; jika kamu pakai barryvdh/laravel-dompdf,
        // cukup ganti return di bawah dengan DomPDF::loadView(...)->stream(...)
        return view('invoices.pdf', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('ok', 'Invoice deleted.');
    }
}
