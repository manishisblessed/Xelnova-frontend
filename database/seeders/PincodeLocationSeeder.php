<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PincodeLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the CSV file
        // Assuming the file is located in specdata/ relative to project root
        // or we can move it to database/seeders/data/
        $csvPath = base_path('specdata/5c2f62fe-5afa-4119-a499-fec9d604d5bd.csv');

        if (File::exists($csvPath)) {
            $this->command->info('Seeding Pincode Locations from CSV...');
            
            // Call the artisan command
            Artisan::call('import:pincodes', ['file' => $csvPath]);
            
            $output = Artisan::output();
            $this->command->info($output);
        } else {
            $this->command->warn('Pincode CSV file not found at: ' . $csvPath);
            $this->command->warn('Skipping Pincode seeding.');
        }
    }
}
