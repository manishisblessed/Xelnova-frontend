<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['name' => 'GST 0% (Exempt)', 'rate' => 0.00],
            ['name' => 'GST 4%', 'rate' => 4.00],
            ['name' => 'GST 5%', 'rate' => 5.00],
            ['name' => 'GST 12%', 'rate' => 12.00],
            ['name' => 'GST 18%', 'rate' => 18.00],
            ['name' => 'GST 28%', 'rate' => 28.00],
        ];

        foreach ($rates as $rate) {
            TaxRate::firstOrCreate(['rate' => $rate['rate']], $rate);
        }
    }
}
