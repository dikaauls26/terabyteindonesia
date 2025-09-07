<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Site;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $r)
    {
        // ====== SUMMARY CARDS ======
        $totalCustomers = Customer::count();
        $statusCounts = Customer::select('service_status', DB::raw('COUNT(*) as c'))
            ->groupBy('service_status')
            ->pluck('c','service_status');

        $activeCount     = (int)($statusCounts['active'] ?? 0);
        $suspendCount    = (int)($statusCounts['suspend'] ?? 0);
        $terminatedCount = (int)($statusCounts['terminated'] ?? 0);

        $totalSites = Site::count();
        $totalPlans = Plan::count();

        // Estimasi MRR dari customers aktif: sum(plan.price_inc_ppn)
        $mrr = Customer::where('service_status', 'active')
            ->join('plans', 'customers.plan_id','=','plans.id')
            ->sum('plans.price_inc_ppn');

        // ARPU (Average Revenue per User) per bulan
        $arpu = $activeCount > 0 ? round($mrr / $activeCount) : 0;

        // Rata-rata bandwidth pelanggan aktif
        $avgBandwidth = Customer::where('service_status','active')
            ->join('plans','customers.plan_id','=','plans.id')
            ->avg('plans.bandwidth_mbps');
        $avgBandwidth = $avgBandwidth ? round($avgBandwidth, 1) : 0;

        // ====== CHART: INSTALLED PER MONTH (12 months) ======
        $startMonth = Carbon::now()->startOfMonth()->subMonths(11); // 12 bulan terakhir
        $installsRaw = Customer::select(
                DB::raw("DATE_FORMAT(installed_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as c')
            )
            ->whereNotNull('installed_at')
            ->where('installed_at', '>=', $startMonth->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $seriesInstalls = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $startMonth->copy()->addMonths($i)->format('Y-m');
            $labels[] = $m;
            $seriesInstalls[] = (int)($installsRaw[$m]->c ?? 0);
        }

        // ====== DISTRIBUSI PLAN (Top 8) ======
        $planDist = Customer::select('plans.name', DB::raw('COUNT(customers.id) as c'))
            ->join('plans','customers.plan_id','=','plans.id')
            ->groupBy('plans.name')
            ->orderByDesc('c')
            ->limit(8)
            ->get();
        $planLabels = $planDist->pluck('name');
        $planSeries = $planDist->pluck('c');

        // ====== STATUS PIE ======
        $statusLabels = ['active','suspend','terminated'];
        $statusSeries = [
            $activeCount,
            $suspendCount,
            $terminatedCount,
        ];

        // ====== HEATMAP POINTS (lat,lng,weight) ======
        // weight berdasarkan harga plan (dinormalisasi 0..1)
        $priceMin = Plan::min('price_inc_ppn') ?: 0;
        $priceMax = Plan::max('price_inc_ppn') ?: 1;
        $priceSpan = max(1, $priceMax - $priceMin);

        $heatPoints = Customer::select(
                'customers.latitude','customers.longitude','plans.price_inc_ppn'
            )
            ->join('plans','customers.plan_id','=','plans.id')
            ->whereNotNull('customers.latitude')
            ->whereNotNull('customers.longitude')
            ->limit(10000)
            ->get()
            ->map(function($row) use ($priceMin, $priceSpan){
                $w = ($row->price_inc_ppn - $priceMin) / $priceSpan;
                // fallback weight minimal 0.2 agar titik tetap terlihat
                $w = max(0.2, min(1.0, $w));
                return [
                    'lat' => (float)$row->latitude,
                    'lng' => (float)$row->longitude,
                    'w'   => $w,
                ];
            });

        // ====== HOTSPOT GRID (prospek area) ======
        // Grid 0.01 derajat (~1.1km); hitung jumlah titik & avg price
        $gridSize = 0.01;
        $hotRows = Customer::select(
                DB::raw('FLOOR(latitude / '.$gridSize.') * '.$gridSize.' as glat'),
                DB::raw('FLOOR(longitude / '.$gridSize.') * '.$gridSize.' as glng'),
                DB::raw('COUNT(*) as cnt'),
                DB::raw('AVG(plans.price_inc_ppn) as avg_price')
            )
            ->join('plans','customers.plan_id','=','plans.id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->groupBy('glat','glng')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get()
            ->map(function($r){
                // pusat cell untuk map
                return [
                    'center_lat' => (float)$r->glat + 0.005,
                    'center_lng' => (float)$r->glng + 0.005,
                    'count'      => (int)$r->cnt,
                    'avg_price'  => (int)round($r->avg_price),
                ];
            });

        // ====== TOP SITES (by customers) ======
        $topSites = Customer::select('sites.name', DB::raw('COUNT(customers.id) as c'))
            ->join('sites','customers.site_id','=','sites.id')
            ->groupBy('sites.name')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        // lempar ke view
        return view('home', [
            'totalCustomers' => $totalCustomers,
            'activeCount'    => $activeCount,
            'suspendCount'   => $suspendCount,
            'terminatedCount'=> $terminatedCount,
            'totalSites'     => $totalSites,
            'totalPlans'     => $totalPlans,
            'mrr'            => (int)$mrr,
            'arpu'           => (int)$arpu,
            'avgBandwidth'   => $avgBandwidth,

            'labels'         => $labels,
            'seriesInstalls' => $seriesInstalls,

            'planLabels'     => $planLabels,
            'planSeries'     => $planSeries,

            'statusLabels'   => $statusLabels,
            'statusSeries'   => $statusSeries,

            'heatPoints'     => $heatPoints,
            'hotRows'        => $hotRows,
            'topSites'       => $topSites,
        ]);
    }
}
