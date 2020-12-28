<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Utility;
use App\Option;

class ProcessReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process tests, cases etc. for day-to-day changes and totals';

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

        $option_last = 'report_last_processed';
        $last_run = Option::get($option_last);
        $curr_env = config('app.env');

        $this->line('');

        $this->line("     ___  _______  ____  ___ __________");
        $this->line("    / _ \/ __/ _ \/ __ \/ _ \_  __/ __/");
        $this->line("   / , _/ _// ___/ /_/ / , _// / _\ \  ");
        $this->line("  /_/|_/___/_/   \____/_/|_|/_/ /___/  ");

        $this->line('');

        $this->line(' # <fg=black;bg=white>Report data processing utility</>');
        $this->line(" # COVID-19 Tracker API v1.0 #");
        // $this->line(' # github.com/andrewthong/covid19tracker-api');

        $this->line('');
        $this->line(" # Environment: <fg=yellow>${curr_env}</>");
        $this->line(" # Last Run: <fg=yellow>${last_run}</>");//${last_run}");

        // prompt
        $mode_from = $this->choice('Process reports starting from', [
            1 => 'Today',
            2 => 'Yesterday',
            3 => 'Last week',
            4 => 'Custom date',
            0 => 'The beginning',
        ], 2);

        $mode_opt = null;
        switch ($mode_from) {
            case 'Yesterday':
                $mode_opt = 1;
                break;
            case 'Last week':
                $mode_opt = 7;
                break;
            case 'Custom date':
                $mode_opt = $this->ask('Please provide date (format: YYYY-MM-DD e.g. 2020-01-15)');
                break;
            case 'The beginning':
                $mode_opt = 'all';
                break;
            default: // today
                $mode_opt = null;
                break;
        }

        // province
        $province = null;
        $choice_province = $this->choice('Would you like to process all Provinces?', [
            1 => 'Yes',
            2 => 'No',
        ], 1);

        if( $choice_province !== 'Yes' ) {
            $province = $this->ask('Please enter a province code (e.g. SK)');
        }

        $mode = Utility::processReportsMode( $mode_opt );

        $this->output->write(' >> Starting process...');
            $this->line(' testing db connection');
        $this->line('');

        // process change_{stat}s (cases, fatalities)
        $this->processReportChanges( $mode, $province );
        $this->line('');

        // process total_{stat}s (tests, hospitalizations, criticals, recoveries)
        $this->processReportTotals( $mode, $province );
        $this->line('');

        // fill in gaps (change <-> total)
        $this->processReportGaps( $mode, $province );
        $this->line('');

        $this->line(' Finising up...');

        Option::set( $option_last, date('Y-m-d H:i:s') );

        $this->line(" <fg=green;bg=black>Processing complete. Reports up to date.</>");
        $this->line('');
        $this->line(' Have a nice day ツ');
        $this->line('');

    }

    /**
     * helper function to determine [date-scope] mode
     * essentially defines from when process scripts should start
     * supports
     *  - 'Y-m-d'
     *  - integer days (will substract days from [today])
     *  - 'all'
     * defaults to [today]
     */
    public function processReportsMode( $mode = null ) {

        $from_date = null;
        
        // check if Y-m-d
        if( preg_match('/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $mode) ) {
            $from_date = $mode;
        }
        // check if integer
        else if( is_int($mode) && $mode >= 1 && $mode <= 90 ) {
            $from_date = date('Y-m-d', strtotime("-{$mode} days"));
        }
        // run on all
        else if( $mode === 'all' ) {
            $from_date = false;
        }
        // defaults to today
        else {
            $from_date = date('Y-m-d');
        }

        return $from_date;
    }

    /**
     * changes are data that is stored on an individual basis
     * cases and fatalities by default
     * this sub-helper calculates daily changes for processedReports
     */
    public function processReportChanges( $mode = null, $province = null ) {

        $from_date = $mode;

        // only for registered provinces, include non-geographic
        $province_codes = Common::getProvinceCodes( false );
        if( in_array($province, $province_codes) ) {
            $province_codes = [$province];
        }

        $where_core = ["`date` IS NOT NULL"];
        
        // only include known provinces
        $provinces_in = implode( "','", $province_codes );
        $where_core[] = "province IN ('{$provinces_in}')";

        // only process records on or after this date
        if( $from_date ) {
            $where_core[] = "`date` >= '{$from_date}'";
        }

        // prepare statement
        $where_stmt = "WHERE ".implode(" AND ", $where_core);

        // query to count daily cases and fatalities from individual db
        $records = DB::select("
            SELECT
                province,
                day,
                COUNT(c_id) as cases,
                COUNT(f_id) as fatalities
            FROM (
                SELECT
                    province,
                    DATE(`date`) AS day,
                    id AS c_id,
                    null AS f_id
                FROM 
                    `cases`
                {$where_stmt}
                UNION
                SELECT
                    province,
                    DATE(`date`) AS day,
                    null as c_id,
                    id AS f_id
                FROM
                    `fatalities`
                {$where_stmt}
            ) AS un
            GROUP BY
                day,
                province
            ORDER BY
                day
        ");

        // [artisan]
        $this->line(" Calculating day-to-day changes");
        $this->line(" (cases, fatalities)");
        $bar = $this->output->createProgressBar( count($records) );
        $bar->start();

        foreach( $records as $record ) {
            DB::table('processed_reports')
                ->updateOrInsert(
                    [
                        'date' => $record->day,
                        'province' => $record->province
                    ],
                    [
                        'date' => $record->day,
                        'province' => $record->province,
                        'change_cases' => $record->cases,
                        'change_fatalities' => $record->fatalities,
                    ]
                );
            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->line(" Calculations complete >>");
    }

    /**
     * totals are data that is stored in the reports log
     * they are an accumulate total of tracked stats
     * this sub-helper moves these totals to processedReports
     */
    public function processReportTotals( $mode = null, $province = null ) {

        // determine date to run on based on mode
        $from_date = $mode;

        // only for registered provinces
        $province_codes = Common::getProvinceCodes();
        if( in_array($province, $province_codes) ) {
            $province_codes = [$province];
        }

        // retrieve reports
        $reports = DB::table( 'reports' )
            ->whereIn( 'province', $province_codes )
            ->when( $from_date, function( $query ) use( $from_date ) {
                $query->where( 'date', '>=', $from_date );
            })
            ->orderBy('date')
            ->get();

        // [artisan]
        $this->line(" Transferring daily totals");
        $this->line(" (tests, hospitalizations, criticals, recoveries, vaccinations)");
        $bar = $this->output->createProgressBar( count($reports) );
        $bar->start();

        // loop through reports and copy records over
        foreach( $reports as $report) {
            DB::table('processed_reports')
                ->updateOrInsert(
                    [
                        'date' => $report->date,
                        'province' => $report->province
                    ],
                    [
                        'date' => $report->date,
                        'province' => $report->province,
                        'total_tests' => $report->tests,
                        'total_hospitalizations' => $report->hospitalizations,
                        'total_criticals' => $report->criticals,
                        'total_recoveries' => $report->recoveries,
                        'total_vaccinations' => $report->vaccinations,
                        'total_vaccines_distributed' => $report->vaccines_distributed,
                        'notes' => $report->notes,
                    ]
                );

            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->line(" Transfers complete >>");

        
    }

    /**
     * this sub-helper runs through process reports and attempts
     * to fill in incomplete change_ and total_ numbers
     */
    public function processReportGaps( $mode = null, $province = null ) {

        // determine date to run on based on mode
        $from_date = $mode;

        // list of provinces
        $province_codes = Common::getProvinceCodes();
        if( in_array($province, $province_codes) ) {
            $province_codes = [$province];
        }

        // core attributes
        $core_attrs = Common::attributes(null, true);
        // attributes where change is expected and total must be calculated
        $change_attrs = array_slice( $core_attrs, 0, 2 );
        // attributes where total is expected and change must be calculated
        $total_attrs = array_slice( $core_attrs, 2 );

        $change_prefix = 'change_';
        $total_prefix = 'total_';
        $reset_value = 0;

        // control, starter to compare to
        $reset_arr = [];
        foreach( [$total_prefix, $change_prefix] as $prefix ) {
            foreach( $core_attrs as $attr ) {
                $reset_arr[$prefix.$attr] = $reset_value; 
            }
        }
        $reset_obj = (object) $reset_arr; // simplifying for later

        // count total
        $total_reports = DB::table( 'processed_reports' )
            ->where( 'date', '>=', $from_date )
            ->when( $province, function( $query ) use( $province ) {
                $query->where('province', '=', $province);
            })
            ->count();

        // [artisan]
        $this->output->write(' Locating reports... ');
        $this->line("{$total_reports} found");
        $this->line(" Calculating numbers (changes <-> totals)...");
        $bar = $this->output->createProgressBar( $total_reports );
        $bar->start();

        // loop through each province code
        foreach( $province_codes as $pc ) {

            // retrieve processed reports
            $reports = DB::table( 'processed_reports' )
                ->where( 'province', '=', $pc )
                ->when( $from_date, function( $query ) use( $from_date ) {
                    $query->where( 'date', '>=', $from_date );
                })
                ->orderBy( 'date' )
                ->get();

            // attempt to retrieve a backtrack reference
            // defaults to our trusted 0 reset otherwise
            $backtrack = clone $reset_obj;
            if( $from_date ) {
                $bt = DB::table( 'processed_reports' )
                    ->where( 'province', '=', $pc )
                    ->where( 'date', '<', $from_date )
                    ->orderBy( 'date', 'desc' )
                    ->first();
                if( $bt ) $backtrack = $bt;
            }

            // now let's loop through each report
            foreach( $reports as $report ) {
                $update_arr = [];
                // calculate total_ from change_
                foreach( $change_attrs as $attr ) {
                    $ch_attr = $change_prefix.$attr;
                    $tt_attr = $total_prefix.$attr;
                    // add current change with w/ backtrack total
                    $update_arr[ $tt_attr ] = 
                          $backtrack->{$tt_attr}
                        + $report->{$ch_attr};
                    $report->{$tt_attr} = $update_arr[ $tt_attr ];
                }
                // calculate change_ from total_
                foreach( $total_attrs as $attr ) {
                    $ch_attr = $change_prefix.$attr;
                    $tt_attr = $total_prefix.$attr;
                    // gaps can introduce weird results
                    if( is_null($report->{$tt_attr}) ) {
                        // set it to backtrack value so change is 0
                        $update_arr[ $tt_attr ] = $backtrack->{$tt_attr};
                        $report->{$tt_attr} = $update_arr[ $tt_attr ];
                    }
                    // subtract current total w/ backtrack total
                    $update_arr[ $ch_attr ] =
                          $report->{$tt_attr}
                        - $backtrack->{$tt_attr};
                    $report->{$ch_attr} = $update_arr[ $ch_attr ];
                }
                // report is now new backtrack
                $backtrack = clone $report;

                // update db
                DB::table('processed_reports')
                    ->where( 'id', '=', $report->id )
                    ->update( $update_arr );

                $bar->advance();
            }

        }

        $bar->finish();
        $this->line("");
        $this->line(" Calculations complete >>");

    }

}//class