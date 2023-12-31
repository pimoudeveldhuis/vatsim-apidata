<?php

namespace App\Console\Commands;

use App\Models\Airport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImportAirports
 *
 * This command should only be run once when setting up the system. It truncates the airports table and inserts all
 *     airports from the configs/import/airports.csv file.
 */
class ImportAirports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:airports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import airports into the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve the Airport CSV from storage, explode it to an array of lines and remove the headerline
        $csvfile = Storage::get('configs/imports/airports.csv');
        $csvfile_lines = explode("\n", $csvfile);
        $csvfile_header = array_shift($csvfile_lines);

        // Truncate the existing table to make sure it is empty
        Airport::truncate();

        foreach($csvfile_lines AS $csvfile_line) {
            if($csvfile_line != '') {
                // Retrieve the CSV data from the line
                $adata = str_getcsv($csvfile_line);

                // Create the airport
                Airport::create([
                    'icao' => $adata[4],
                    'iata' => ($adata[3] !== '\N') ? $adata[3] : null,

                    'name' => ($adata[0] !== '\N') ? $adata[0] : null,
                    'city' => ($adata[1] !== '\N') ? $adata[1] : null,
                    'country' => ($adata[2] !== '\N') ? $adata[2] : null,

                    'long' => ($adata[5] !== '\N') ? $adata[5] : null,
                    'lat' => ($adata[6] !== '\N') ? $adata[6] : null,

                    'altitude' => ($adata[7] !== '\N') ? $adata[7] : null,
                    'timezone' => ($adata[10] !== '\N') ? $adata[10] : null,
                ]);
            }
        }
    }
}
