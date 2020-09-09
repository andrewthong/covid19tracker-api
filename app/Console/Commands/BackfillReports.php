<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DateTime;

use App\Common;
use App\Utility;
use App\Report;
use App\HrReport;

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
        $curr_env = config('app.env');

        $this->line('');

        $this->line("     ___  ___  _______ ____________   __ ");
        $this->line("    / _ )/ _ |/ ___/ //_/ __/  _/ /  / / ");
        $this->line("   / _  / __ / /__/ ,< / _/_/ // /__/ /__");
        $this->line("  /____/_/ |_\___/_/|_/_/ /___/____/____/");

        $this->line('');

        $this->line(' # <fg=black;bg=white>Backfill utility (Report/HR Report)</>');
        $this->line(' # COVID-19 Tracker API Database v1.0 #');
        // $this->line(' # github.com/andrewthong/covid19tracker-api');
        $this->line('');
        $this->line(" # Environment: <fg=yellow>${curr_env}</>");

        $report_type = $this->choice('Backfill for', [
            1 => 'Reports',
            2 => 'HR Reports',
        ], 1);

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
        $location_codes = [];
        $report_singular = 'report';
        $location_col = 'province';
        $dates = [];
        $created = 0;

        // report type
        if( $report_type === 'HR Reports' ) {
            $location_codes = Common::getHealthRegionCodes();
            $report_singular = 'health region report';
            $location_col = 'hr_uid';
        } else {
            $location_codes = Common::getProvinceCodes();
        }

        // [artisan]
        $est_total = ($diff->days + 1) * count($location_codes);
        $this->line(" Finding {$report_singular} entries ({$est_total} expected)");
        $bar = $this->output->createProgressBar( $est_total );
        $bar->start();

        // loop through dates
        for( $i = 0; $i <= $diff->days; $i++ ) {
            $d = $start_date->format('Y-m-d');
            $dates[] = $d;
            // increment start date
            $start_date->modify('+1 day');
            foreach( $location_codes as $location ) {
                // simple safeguard against blank province or date
                if( !$location || !$d ) break;
                $obj = [
                    $location_col => $location,
                    'date' => $d
                ];
                $row = null;
                // insert can be null for these missing entries
                if( $report_type === 'HR Reports' ) {
                    $row = HrReport::firstOrCreate( $obj );
                } else {
                    $row = Report::firstOrCreate( $obj );
                }
                // test if row was recently created
                if( $row->wasRecentlyCreated ) {
                    $created++;
                    // additional backfill of case/fatality data
                    if( $report_type === 'HR Reports' ) {
                        $records = Utility::countCaseFatality($d, $location, 'hr_uid', '<=');
                        if( isset($records->cases) && isset($records->fatalities) ) {
                            $row->update([
                                'cases' => $records->cases,
                                'fatalities' => $records->fatalities,
                            ]);
                        }
                    }
                }
                // [artisan]
                $bar->advance();
            }  
        }

        $d1 = $dates[0];
        $d2 = end($dates);

        $this->line('');
        $this->line('');
        $this->line(" <fg=green;bg=black>Backfill complete.</>");
        $this->line(" Added {$created} missing {$report_singular} entries within {$d1} – {$d2}");
        $this->line('');
        $this->line(' Have a nice day ツ');
        $this->line('');

    }
}
