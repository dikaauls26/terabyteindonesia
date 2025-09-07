<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $rows = [];
        for ($i=1; $i<=20; $i++) {
            $rows[] = [
                'name'       => "Site " . $i,
                'code'       => strtoupper(Str::random(4)),
                'address'    => $faker->address,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Site::insert($rows);

        $this->command->info("✅ 20 Sites generated");
    }
}
