<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $rows = [];
        for ($i=1; $i<=20; $i++) {
            $bw   = $faker->randomElement([10,20,50,100,200,300,500,1000]);
            $price= $bw * 10000 + rand(50000,200000);
            $rows[] = [
                'name'          => "Plan {$bw} Mbps",
                'bandwidth_mbps'=> $bw,
                'price_inc_ppn' => $price,
                'is_active'     => $faker->boolean(90),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }
        Plan::insert($rows);

        $this->command->info("✅ 20 Plans generated");
    }
}
