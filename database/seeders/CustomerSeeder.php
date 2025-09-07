<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Site;
use App\Models\Plan;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::pluck('id')->all();
        $plans = Plan::pluck('id')->all();

        if (empty($sites) || empty($plans)) {
            $this->command->warn("❌ Seed sites & plans dulu sebelum customers");
            return;
        }

        $faker    = \Faker\Factory::create('id_ID');
        $statuses = ['active','suspend','terminated'];
        $brands   = ['ZTE','Huawei','Fiberhome','Nokia','Tp-Link'];

        $total    = 1500;
        $batchSize= 200;

        for ($i=0; $i < $total; $i += $batchSize) {
            $rows = [];

            for ($j=0; $j < $batchSize; $j++) {
                // Jakarta / Tangerang 50:50
                if ($faker->boolean(50)) {
                    // Jakarta
                    $lat = $faker->latitude(-6.30, -6.10);
                    $lng = $faker->longitude(106.70, 106.95);
                } else {
                    // Tangerang
                    $lat = $faker->latitude(-6.25, -6.05);
                    $lng = $faker->longitude(106.55, 106.75);
                }

                $rows[] = [
                    'customer_no'     => 'CUST-' . strtoupper(Str::random(6)),
                    'name'            => $faker->name,
                    'email'           => $faker->unique()->safeEmail,
                    'phone'           => $faker->phoneNumber,
                    'site_id'         => $faker->randomElement($sites),
                    'plan_id'         => $faker->randomElement($plans),
                    'is_active'       => $faker->boolean(80),
                    'notes'           => $faker->sentence(6),
                    'ont_brand'       => $faker->randomElement($brands),
                    'ont_sn'          => strtoupper(Str::random(12)),
                    'latitude'        => $lat,
                    'longitude'       => $lng,
                    'installed_at'    => $faker->dateTimeBetween('-2 years', 'now'),
                    'technician_name' => $faker->name,
                    'service_status'  => $faker->randomElement($statuses),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            Customer::insert($rows);
        }

        $this->command->info("✅ 1500 Customers generated (Jakarta & Tangerang mix).");
    }
}
