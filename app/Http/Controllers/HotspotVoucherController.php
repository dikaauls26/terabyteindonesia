<?php

namespace App\Http\Controllers;

use App\Models\HotspotVoucher;
use Illuminate\Http\Request;

class HotspotVoucherController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->input('status');
        $q      = trim((string)$r->input('q'));

        $rows = HotspotVoucher::query()
            ->when($status, fn($w) => $w->where('status', $status))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where(function ($s) use ($like) {
                    $s->where('code', 'like', $like)
                        ->orWhere('profile', 'like', $like)
                        ->orWhere('buyer_email', 'like', $like)
                        ->orWhere('buyer_phone', 'like', $like)
                        ->orWhere('batch_id', 'like', $like)
                        ->orWhere('router', 'like', $like);
                });
            })
            ->orderBy('status')
            ->orderBy('profile')
            ->orderBy('price')
            ->paginate(25)
            ->withQueryString();

        return view('vouchers.index', compact('rows', 'status', 'q'));
    }

    public function create()
    {
        return view('vouchers.create');
    }

    public function store(Request $r)
    {
        // mode tunggal atau bulk
        if ($r->filled('bulk_codes')) {
            $data = $r->validate([
                'bulk_codes' => 'required|string', // satu kode per baris: CODE|PROFILE|DURATION_MIN|QUOTA_MB|PRICE
                'batch_id'   => 'nullable|string|max:50',
                'router'     => 'nullable|string|max:100',
                'is_active'  => 'nullable|boolean',
            ]);
            $isActive = (bool)($data['is_active'] ?? true);
            $lines = preg_split('/\r\n|\r|\n/', trim($data['bulk_codes']));
            $created = 0;
            $skipped = 0;

            foreach ($lines as $line) {
                if ($line === '') continue;
                // format fleksibel: "CODE" atau "CODE|PROFILE|DURATION|QUOTA|PRICE"
                $parts = array_map('trim', explode('|', $line));
                $code  = $parts[0] ?? null;
                if (!$code) {
                    $skipped++;
                    continue;
                }

                if (HotspotVoucher::where('code', $code)->exists()) {
                    $skipped++;
                    continue;
                }

                HotspotVoucher::create([
                    'code' => $code,
                    'profile' => $parts[1] ?? null,
                    'duration_minutes' => isset($parts[2]) && is_numeric($parts[2]) ? (int)$parts[2] : null,
                    'quota_mb' => isset($parts[3]) && is_numeric($parts[3]) ? (int)$parts[3] : null,
                    'price' => isset($parts[4]) && is_numeric($parts[4]) ? (int)$parts[4] : 0,
                    'currency' => 'IDR',
                    'status' => 'available',
                    'batch_id' => $data['batch_id'] ?? null,
                    'router'   => $data['router'] ?? null,
                    'is_active' => $isActive,
                ]);
                $created++;
            }

            return redirect()->route('vouchers.index')->with('ok', "Import selesai. Created: {$created}, Skipped: {$skipped}");
        }

        // mode single
        $data = $r->validate([
            'code' => 'required|string|max:150|unique:hotspot_vouchers,code',
            'profile' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'quota_mb' => 'nullable|integer|min:0',
            'price' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:10',
            'batch_id' => 'nullable|string|max:50',
            'router' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);
        $data['status'] = 'available';
        $data['currency'] = $data['currency'] ?? 'IDR';
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        HotspotVoucher::create($data);
        return redirect()->route('vouchers.index')->with('ok', 'Voucher created');
    }

    public function edit(HotspotVoucher $voucher)
    {
        return view('vouchers.edit', ['v' => $voucher]);
    }

    public function update(Request $r, HotspotVoucher $voucher)
    {
        $data = $r->validate([
            'code' => 'required|string|max:150|unique:hotspot_vouchers,code,' . $voucher->id,
            'profile' => 'nullable|string|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'quota_mb' => 'nullable|integer|min:0',
            'price' => 'required|integer|min:0',
            'currency' => 'nullable|string|max:10',
            'batch_id' => 'nullable|string|max:50',
            'router' => 'nullable|string|max:100',
            'status' => 'required|in:available,reserved,sold,redeemed,expired,disabled',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);
        $data['currency'] = $data['currency'] ?? 'IDR';
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $voucher->update($data);
        return redirect()->route('vouchers.index')->with('ok', 'Voucher updated');
    }

    public function destroy(HotspotVoucher $voucher)
    {
        $voucher->delete();
        return back()->with('ok', 'Voucher deleted');
    }
}
