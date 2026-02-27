<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PincodeLocation;
use Illuminate\Support\Facades\DB;

class ImportPincodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pincodes {file : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import pincodes from a CSV file into the pincode_locations table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Starting import from {$file}...");

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Skip header row: circlename,regionname,divisionname,officename,pincode,officetype,delivery,district,statename,latitude,longitude

        $batchSize = 1000;
        $batch = [];
        $count = 0;
        $totalImported = 0;

        $this->output->progressStart(165000); //Approx lines

        DB::beginTransaction();

        try {
            $processedPincodes = [];

            while (($row = fgetcsv($handle)) !== false) {
                // Map CSV columns to DB columns
                // CSV: 0:circle, 1:region, 2:division, 3:office, 4:pincode, 5:type, 6:delivery, 7:district, 8:state, 9:lat, 10:long
                
                $pincode = trim($row[4]);
                // Normalize pincode: remove spaces, dots, dashes
                $pincode = preg_replace('/[^0-9]/', '', $pincode);

                $city = $row[7]; // district
                $state = $row[8]; // statename
                $lat = (float)$row[9];
                $lng = (float)$row[10];

                // Skip if lat/long is NA or missing or invalid
                if (empty($lat) || empty($lng) || !is_numeric($lat) || !is_numeric($lng)) {
                    continue;
                }

                // Check for valid lat/long ranges
                if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    // Log invalid data if needed, or just skip
                    continue;
                }

                // Skip invalid pincodes (must be 6 digits)
                if (strlen($pincode) !== 6) {
                    continue;
                }

                // Deduplicate within this run implementation effectively not needed if we use updateOrCreate
                // But good to skip redundant DB calls
                if (isset($processedPincodes[$pincode])) {
                    continue;
                }
                $processedPincodes[$pincode] = true;

                PincodeLocation::updateOrCreate(
                    ['pincode' => $pincode],
                    [
                        'latitude' => (float)$lat,
                        'longitude' => (float)$lng,
                        'city' => $city,
                        'state' => $state,
                        'country' => 'IN',
                    ]
                );

                $totalImported++;
                $this->output->progressAdvance();
            }

            DB::commit();
            $this->output->progressFinish();
            $this->info("Import completed successfully! Total records: {$totalImported}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            return 1;
        } finally {
            fclose($handle);
        }

        return 0;
    }
}
