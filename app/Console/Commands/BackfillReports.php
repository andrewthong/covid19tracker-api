<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DateTime;

use App\Common;
use App\Report;

class BackfillReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:backfill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills in any missing date entries between provided start and end dates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('');

        $this->line("     ___  _______  ____  ___ __________");
        $this->line("    / _ \/ __/ _ \/ __ \/ _ \_  __/ __/");
        $this->line("   / , _/ _// ___/ /_/ / , _// / _\ \  ");
        $this->line("  /_/|_/___/_/   \____/_/|_|/_/ /___/  ");

        $this->line('');

        $this->line(' # <fg=black;bg=white>COVID-19 Tracker API Database v0.7</> #');
        $this->line(' # Report backfill utility');

        $start_date = $this->ask('Start date (format: YYYY-MM-DD e.g. 2020-01-15)');
        $end_date = $this->ask('End date (format: YYYY-MM-DD e.g. 2020-02-15)');

        // validate
        $valid = true;
        $start_date = new DateTime( $start_date );
        $end_date = new DateTime( $end_date );

        $diff = $start_date->diff( $end_date );

        if( $diff->format("%r%a") < 1 ) {
            $this->line(' <bg=red>End date must be ahead of start date</>');
            $valid = false;
        }

        // date range validation
        $start_year = (int) $start_date->format('U');
        $end_year = (int) $end_date->format('U');
        // this might need to be migrated to a config setting
        $min_date = '2019-01-01';
        $min_u = strtotime($min_date);
        $max_u = strtotime('tomorrow');
        $max_date = date('Y-m-d', $max_u);
        // prevent creating reports before $min_year
        if( $start_year < $min_u || $end_year < $min_u ) {
            $this->line(" <bg=red>Dates cannot be before {$min_date}</>");
            $valid = false;
        }
        // prevent creating reports after tomorrow (limited to server time)
        if( $start_year >= $max_u || $end_year >= $max_u ) {
            $this->line(" <bg=red>Dates cannot be after {$max_date} (tomorrow)</>");
            $valid = false;
        }

        if( !$valid ) {
            $this->line(" Errors found; unable to proceed.");
            exit();
        }

        // setup
        $province_codes = Common::getProvinceCodes();
        $dates = [];
        $created = 0;

        // [artisan]
        $est_total = ($diff->days + 1) * count($province_codes);
        $this->line(" Finding report entries ({$est_total} expected)");
        $bar = $this->output->createProgressBar( $est_total );
        $bar->start();

        // loop through dates
        for( $i = 0; $i <= $diff->days; $i++ ) {
            $d = $start_date->format('Y-m-d');
            $dates[] = $d;
            // increment start date
            $start_date->modify('+1 day');
            foreach( $province_codes as $pc ) {
                // simple safeguard against blank province or date
                if( !$pc || !$d ) break;
                $obj = [
                    'province' => $pc,
                    'date' => $d
                ];
                // insert can be null for these missing entries
                $row = Report::firstOrCreate( $obj );
                // test if row was recently created
                if( $row->wasRecentlyCreated )
                    $created++;
                // [artisan]
                $bar->advance();
            }  
        }

        $d1 = $dates[0];
        $d2 = end($dates);

        $this->line('');
        $this->line('');
        $this->line(" <fg=green;bg=black>Backfill complete.</>");
        $this->line(" Added {$created} missing reports within {$d1} – {$d2}");
        $this->line('');
        $this->line(' Have a nice day ツ');
        $this->line('');

    }
}
