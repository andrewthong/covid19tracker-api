<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Flynsarmy\CsvSeeder\CsvSeeder;

use DB;

class SubRegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table = 'sub_regions';

        // truncate: comment out if using to add data
        // DB::table($table)->truncate();

        // use csv to seed database
        $seed_csv = base_path()."/database/seeders/csv/{$table}.csv";
        if( file_exists( $seed_csv ) ) {
            $seeder = new \Flynsarmy\CsvSeeder\CsvSeeder;
            $seeder->table = $table;
            $seeder->filename = $seed_csv;
            $seeder->run();
        }
    }
}
