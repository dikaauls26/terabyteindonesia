<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    /**
     * Tampilkan daftar sites (untuk Blade: resources/views/sites/index.blade.php)
     */
    public function index()
    {
        $sites = Site::latest()->get();
        return view('sites.index', compact('sites'));
    }

    /**
     * Simpan site baru (AJAX JSON)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'    => ['required', 'string', 'max:150'],
                'code'    => ['required', 'string', 'max:100', Rule::unique('sites', 'code')],
                'address' => ['nullable', 'string'],
            ]);

            $site = Site::create($data);

            return response()->json(['ok' => true, 'data' => $site]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            // kembalikan pesan validasi lebih jelas
            return response()->json(['ok' => false, 'message' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update site (AJAX JSON)
     */
    public function update(Request $request, Site $site)
    {
        try {
            $data = $request->validate([
                'name'    => ['required', 'string', 'max:150'],
                'code'    => ['required', 'string', 'max:100', Rule::unique('sites', 'code')->ignore($site->id)],
                'address' => ['nullable', 'string'],
            ]);

            $site->update($data);

            return response()->json(['ok' => true, 'data' => $site]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['ok' => false, 'message' => $ve->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hapus site (AJAX JSON)
     */
    public function destroy(Site $site)
    {
        try {
            $site->delete();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
