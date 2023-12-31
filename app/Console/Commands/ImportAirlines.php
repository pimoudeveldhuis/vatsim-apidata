<?php

namespace App\Console\Commands;

use App\Models\Airline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImportAirlines
 *
 * This command should only be run once when setting up the system. It truncates the airline table and inserts all
 *     airlines from the configs/import/airlines.csv file.
 */
class ImportAirlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:airlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import airlines into the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve the Airline CSV from storage, explode it to an array of lines and remove the headerline
        $csvfile = Storage::get('configs/imports/airlines.csv');
        $csvfile_lines = explode("\n", $csvfile);
        $csvfile_header = array_shift($csvfile_lines);

        // Truncate the existing table to make sure it is empty
        Airline::truncate();

        foreach($csvfile_lines AS $csvfile_line) {
            if($csvfile_line != '') {
                // Retrieve the CSV data from the line
                $adata = str_getcsv($csvfile_line);

                // Create the airline
                Airline::create([
                    'icao' => ($adata[2] !== '') ? $adata[2] : null,
                    'iata' => ($adata[1] !== '') ? $adata[1] : null,

                    'name' => ($adata[0] !== '') ? $adata[0] : null,
                    'callsign' => ($adata[3] !== '') ? $adata[3] : null,
                    'country' => ($adata[4] !== '') ? $adata[4] : null,

                    'active' => ($adata[5] === 'Y') ? true : false,
                ]);
            }
        }
    }
}
