<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Site, Plan};


class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Site::firstOrCreate(['code' => 'GCA-A'], ['name' => 'GCA Tower A', 'address' => null]);
        Plan::firstOrCreate(['bandwidth_mbps' => 30], ['name' => 'Paket 30 Mbps', 'price_inc_ppn' => 350000, 'is_active' => true]);
    }
}
