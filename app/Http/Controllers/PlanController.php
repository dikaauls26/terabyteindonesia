<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /**
     * Tampilkan daftar plans (untuk Blade: resources/views/plans/index.blade.php)
     */
    public function index()
    {
        $plans = Plan::latest()->get();
        return view('plans.index', compact('plans'));
    }

    /**
     * Simpan plan baru (AJAX JSON)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'            => ['required', 'string', 'max:150'],
                'bandwidth_mbps'  => ['required', 'integer', 'min:1'],
                'price_inc_ppn'   => ['required', 'integer', 'min:0'],
                'is_active'       => ['sometimes', 'boolean'],
            ]);

            $data['is_active'] = (int)($data['is_active'] ?? 0);

            $plan = Plan::create($data);

            return response()->json(['ok' => true, 'data' => $plan]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update plan (AJAX JSON)
     */
    public function update(Request $request, Plan $plan)
    {
        try {
            $data = $request->validate([
                'name'            => ['required', 'string', 'max:150'],
                'bandwidth_mbps'  => ['required', 'integer', 'min:1'],
                'price_inc_ppn'   => ['required', 'integer', 'min:0'],
                'is_active'       => ['sometimes', 'boolean'],
            ]);

            $data['is_active'] = (int)($data['is_active'] ?? 0);

            $plan->update($data);

            return response()->json(['ok' => true, 'data' => $plan]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus plan (AJAX JSON)
     */
    public function destroy(Plan $plan)
    {
        try {
            $plan->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
