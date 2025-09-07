<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\HotspotVoucher;


class PublicController extends Controller
{

    public function welcome(Request $r)
    {
        // ambil top paket (distinct kombinasi yang “dipasarkan”)
        $products = HotspotVoucher::available()
            ->select('profile', 'duration_minutes', 'quota_mb', 'price')
            ->groupBy('profile', 'duration_minutes', 'quota_mb', 'price')
            ->orderBy('price')
            ->limit(8)
            ->get();

        return view('welcome', [
            'query'    => null,
            'invoices' => collect(),
            'error'    => null,
            'products' => $products,
        ]);
    }

    public function checkBills(Request $r)
    {
        $data = $r->validate([
            'identifier' => ['required', 'string', 'max:150'], // email atau customer_no
            'month'      => ['nullable', 'date_format:Y-m'],
            'only_unpaid' => ['nullable', 'boolean'],
        ]);

        $identifier = trim($data['identifier']);
        $month      = $data['month'] ?? null;
        $onlyUnpaid = (bool)($data['only_unpaid'] ?? false);

        // Cari berdasarkan email ATAU customer_no di snapshot invoice
        $q = Invoice::query()
            ->when(str_contains($identifier, '@'), function ($qq) use ($identifier) {
                $qq->where('customer_email', $identifier);
            }, function ($qq) use ($identifier) {
                $qq->where('customer_no', $identifier)  // jika kamu menyimpan 'customer_no' di snapshot, pakai ini
                    ->orWhere('customer_name', 'like', '%' . $identifier . '%')
                    ->orWhere('invoice_number', $identifier);
            });

        // Jika belum ada kolom 'customer_no' di invoices, hapus where('customer_no', ...) di atas.
        // Alternatif lookup by customer name atau invoice number sudah disiapkan.

        if ($month) {
            $q->where('billing_month', $month);
        }
        if ($onlyUnpaid) {
            $q->where('status', 'unpaid');
        }

        $invoices = $q->orderByDesc('billing_date')
            ->orderBy('invoice_number')
            ->limit(200) // batasi tampilan publik
            ->get();

        $error = null;
        if ($invoices->isEmpty()) {
            $error = 'Tidak ditemukan tagihan untuk data yang Anda masukkan. Pastikan email atau nomor pelanggan benar.';
        }

        return view('welcome', [
            'query'    => $identifier,
            'invoices' => $invoices,
            'error'    => $error,
            'month'    => $month,
            'onlyUnpaid' => $onlyUnpaid,
        ]);
    }

    // Voucher Checkout
    public function voucherCheckout(Request $r)
    {
        // Placeholder: belum proses pembayaran. Nanti: reserve salah satu voucher available sesuai paket, buat payment link.
        $data = $r->validate([
            'product_profile' => 'required|string',
            'product_price'   => 'required|integer|min:0',
            'buyer_name'      => 'required|string|max:150',
            'buyer_email'     => 'nullable|email',
            'buyer_phone'     => 'required|string|max:50',
        ]);

        // TODO: reserve voucher: HotspotVoucher::available()->where('profile', ...)->where('price', ...)->first()
        // lalu isi buyer_*, status=reserved, reserved_at=now(), arahkan ke payment gateway.

        return back()->with('ok', 'Terima kasih! Pesanan voucher diterima. Kami akan mengirim link pembayaran / instruksi selanjutnya.');
    }
    // Voucher Checkout
}
