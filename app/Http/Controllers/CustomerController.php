<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Site;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Index + Search/Filter + Pagination
     */
    public function index(Request $r)
    {
        $q        = trim((string)$r->input('q'));
        $siteId   = $r->input('site_id');
        $priceMin = $r->filled('price_min') ? (int)$r->input('price_min') : null;
        $priceMax = $r->filled('price_max') ? (int)$r->input('price_max') : null;

        $query = Customer::with(['site', 'plan'])->latest();

        // Global search
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('customer_no', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        // Filter Site
        if (!empty($siteId)) {
            $query->where('site_id', $siteId);
        }

        // Filter Harga Plan (price_inc_ppn)
        if (!is_null($priceMin) || !is_null($priceMax)) {
            $query->whereHas('plan', function ($p) use ($priceMin, $priceMax) {
                if (!is_null($priceMin)) $p->where('price_inc_ppn', '>=', $priceMin);
                if (!is_null($priceMax)) $p->where('price_inc_ppn', '<=', $priceMax);
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        $sites          = Site::orderBy('name')->get();
        $priceMinAvail  = Plan::min('price_inc_ppn') ?? 0;
        $priceMaxAvail  = Plan::max('price_inc_ppn') ?? 0;

        return view('customers.index', compact(
            'customers',
            'sites',
            'q',
            'siteId',
            'priceMin',
            'priceMax',
            'priceMinAvail',
            'priceMaxAvail'
        ));
    }

    /**
     * Form create
     */
    public function create()
    {
        return view('customers.create', [
            'sites' => Site::orderBy('name')->get(),
            'plans' => Plan::where('is_active', 1)->orderBy('bandwidth_mbps')->get(),
        ]);
    }

    /**
     * Store
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'customer_no' => ['required', 'string', 'max:100', 'unique:customers,customer_no'],
            'name'        => ['required', 'string', 'max:150'],
            'email'       => ['nullable', 'email', 'max:150'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'site_id'     => ['required', 'exists:sites,id'],
            'plan_id'     => ['required', 'exists:plans,id'],
            'is_active'   => ['sometimes', 'boolean'],
            'notes'       => ['nullable', 'string'],

            'ont_brand'   => ['nullable', 'string', 'max:100'],
            'ont_sn'      => ['nullable', 'string', 'max:150'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'installed_at'   => ['nullable', 'date'],
            'technician_name' => ['nullable', 'string', 'max:150'],
            'service_status' => ['nullable', 'in:active,suspend,terminated'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        Customer::create($data);

        return redirect()->route('customers.index')->with('ok', 'Customer created');
    }

    /**
     * Form edit
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', [
            'c'     => $customer,
            'sites' => Site::orderBy('name')->get(),
            'plans' => Plan::where('is_active', 1)->orderBy('bandwidth_mbps')->get(),
        ]);
    }


    // Show
    public function show(\App\Models\Customer $customer)
    {
        return redirect()->route('customers.edit', $customer);
    }
    // Show
    /**
     * Update
     */
    public function update(Request $r, Customer $customer)
    {
        $data = $r->validate([
            'customer_no' => ['required', 'string', 'max:100', Rule::unique('customers', 'customer_no')->ignore($customer->id)],
            'name'        => ['required', 'string', 'max:150'],
            'email'       => ['nullable', 'email', 'max:150'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'site_id'     => ['required', 'exists:sites,id'],
            'plan_id'     => ['required', 'exists:plans,id'],
            'is_active'   => ['sometimes', 'boolean'],
            'notes'       => ['nullable', 'string'],

            'ont_brand'   => ['nullable', 'string', 'max:100'],
            'ont_sn'      => ['nullable', 'string', 'max:150'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'installed_at'   => ['nullable', 'date'],
            'technician_name' => ['nullable', 'string', 'max:150'],
            'service_status' => ['nullable', 'in:active,suspend,terminated'],
        ]);

        $data['is_active'] = array_key_exists('is_active', $data)
            ? (bool)$data['is_active']
            : (bool)$customer->is_active;

        $customer->update($data);

        return redirect()->route('customers.index')->with('ok', 'Updated');
    }

    /**
     * Destroy
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('ok', 'Deleted');
    }

    public function map(Request $r)
    {
        // dropdown filter
        $sites = \App\Models\Site::orderBy('name')->get();
        $plans = \App\Models\Plan::where('is_active', 1)->orderBy('bandwidth_mbps')->get();

        // default center (Jakarta) bila user belum set
        $defaultLat = -6.2000000;
        $defaultLng = 106.8166667;

        return view('customers.map', compact('sites', 'plans', 'defaultLat', 'defaultLng'));
    }

    /**
     * Endpoint JSON (GeoJSON FeatureCollection) untuk Leaflet
     * Filter:
     * - q
     * - site_id
     * - plan_id
     * - status (service_status): active|suspend|terminated
     * - price_min, price_max (plan.price_inc_ppn)
     * - radius_km + center_lat + center_lng (haversine)
     */
    public function mapData(Request $r)
    {
        $q        = trim((string)$r->input('q'));
        $siteId   = $r->input('site_id');
        $planId   = $r->input('plan_id');
        $status   = $r->input('status'); // service_status
        $priceMin = $r->filled('price_min') ? (int)$r->input('price_min') : null;
        $priceMax = $r->filled('price_max') ? (int)$r->input('price_max') : null;

        $radiusKm = $r->filled('radius_km') ? (float)$r->input('radius_km') : null;
        $centerLat = $r->filled('center_lat') ? (float)$r->input('center_lat') : null;
        $centerLng = $r->filled('center_lng') ? (float)$r->input('center_lng') : null;

        $qBuilder = \App\Models\Customer::with(['site', 'plan'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($q !== '') {
            $qBuilder->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('customer_no', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('ont_sn', 'like', $like)
                    ->orWhere('ont_brand', 'like', $like)
                    ->orWhere('technician_name', 'like', $like);
            });
        }

        if (!empty($siteId)) {
            $qBuilder->where('site_id', $siteId);
        }

        if (!empty($planId)) {
            $qBuilder->where('plan_id', $planId);
        }

        if (!empty($status)) {
            $qBuilder->where('service_status', $status);
        }

        if (!is_null($priceMin) || !is_null($priceMax)) {
            $qBuilder->whereHas('plan', function ($p) use ($priceMin, $priceMax) {
                if (!is_null($priceMin)) $p->where('price_inc_ppn', '>=', $priceMin);
                if (!is_null($priceMax)) $p->where('price_inc_ppn', '<=', $priceMax);
            });
        }

        // Filter radius (haversine)
        if (!is_null($radiusKm) && !is_null($centerLat) && !is_null($centerLng) && $radiusKm > 0) {
            // 6371 = radius bumi (km)
            $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
            $qBuilder->select('*')
                ->selectRaw("$haversine AS distance_km", [$centerLat, $centerLng, $centerLat])
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km', 'asc');
        } else {
            $qBuilder->latest();
        }

        $items = $qBuilder->limit(5000)->get(); // batasi maksimal 5k titik

        // Build GeoJSON
        $features = $items->map(function ($c) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$c->longitude, (float)$c->latitude],
                ],
                'properties' => [
                    'id' => $c->id,
                    'customer_no' => $c->customer_no,
                    'name' => $c->name,
                    'email' => $c->email,
                    'phone' => $c->phone,
                    'site' => optional($c->site)->name,
                    'site_code' => optional($c->site)->code,
                    'plan' => optional($c->plan)->name,
                    'bandwidth_mbps' => optional($c->plan)->bandwidth_mbps,
                    'price_inc_ppn' => optional($c->plan)->price_inc_ppn,
                    'is_active' => (bool)$c->is_active,
                    'service_status' => $c->service_status,
                    'ont_brand' => $c->ont_brand,
                    'ont_sn' => $c->ont_sn,
                    'installed_at' => $c->installed_at,
                    'technician_name' => $c->technician_name,
                    'lat' => (float)$c->latitude,
                    'lng' => (float)$c->longitude,
                    'edit_url' => route('customers.edit', $c),
                    'maps_url' => $c->latitude && $c->longitude ? "https://maps.google.com/?q={$c->latitude},{$c->longitude}" : null,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
