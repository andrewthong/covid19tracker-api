<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Utility;
use App\Option;

use App\VaccineReport;

class FillReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:fill
                            {--table= : table name}
                            {--province= : province code}
                            {--date= : Y-m-d format}
                            {--noclear}
                            {--nolast}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Modular update of report:process';

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
     * @return int
     */
    public function handle()
    {

        // supported tables
        // key = table name
        // set _attrs based on what data_entry will fill; the counterpart is then filled by this script
        $supported_tables = [
            'vaccine_reports' => VaccineReport::referenceAttrs( true ),
        ];

        // options support
        $options = $this->options();
        $province = null;
        $date_opt = null; // used to be mode_opt to support date-less
        
        $curr_env = config('app.env');

        $this->line('');
        $this->line(' Fill Reports (process V2)');
        $this->line(" # Environment: <fg=yellow>${curr_env}</>");

        // table to process
        if( $options['table'] ) {
            $table = $options['table'];
        } else {
            $table = $this->choice( 'Select report to process', array_keys($supported_tables) );
        }

        // validate table
        if( !array_key_exists( $table, $supported_tables ) ) {
            $this->line(" <fg=red>ERROR:</> invalid table");
            return 0;
        }
        $table_data = $supported_tables[$table];
        $table_data['table_name'] = $table;

        // province
        if( $options['province'] ) {
            $province = $options['province'];
        } else {
            $province = $this->ask('Enter the province code to process (e.g. SK)');
        }

        // date
        if( $options['date'] ) {
            $date_opt = $options['date'];
        } else {
            $date_opt = $this->ask('Enter a date process starts from (format: YYYY-MM-DD e.g. 2020-01-15)');
        }

        // when table-specific report was last run
        $option_last = "{$table}_last_processed";
        $last_run = Option::get($option_last);

        $this->output->write(' >> Starting process...');
        $this->line('');

        // fill in gaps (change <-> total)
        $this->processReportGaps( $province, $date_opt, $table_data );
        $this->line('');

        return 0;
    }

    /**
     * this sub-helper runs through table and attempts
     * to fill in incomplete change_ and total_ numbers
     */
    public function processReportGaps( $province, $from_date, $table_data ) {

        // list of provinces
        $province_codes = Common::getProvinceCodes();
        if( in_array($province, $province_codes) ) {
            $province_codes = [$province];
        }

        // attributes where change is expected and total must be calculated
        $change_attrs = $table_data['change_attrs'];
        // attributes where total is expected and change must be calculated
        $total_attrs = $table_data['total_attrs'];
        $core_attrs = array_merge( $change_attrs, $total_attrs );

        $change_prefix = 'change_';
        $total_prefix = 'total_';
        
        $reset_value = null;

        // control, starter to compare to
        $reset_arr = [];
        foreach( [$total_prefix, $change_prefix] as $prefix ) {
            foreach( $core_attrs as $attr ) {
                $reset_arr[$prefix.$attr] = $reset_value; 
            }
        }
        $reset_obj = (object) $reset_arr; // simplifying for later

        // count total
        $total_reports = DB::table( $table_data['table_name'] )
            ->where( 'date', '>=', $from_date )
            ->when( $province, function( $query ) use( $province ) {
                $query->where('province', '=', $province);
            })
            ->count();

        // [artisan]
        $this->output->write(" Fetching {$table_data['table_name']} data...");
        $this->line("{$total_reports} found");
        $this->line(" Calculating numbers (changes <-> totals)...");
        $bar = $this->output->createProgressBar( $total_reports );
        $bar->start();

        // retrieve reports
        $reports = DB::table( $table_data['table_name'] )
            ->where( 'province', '=', $province )
            ->when( $from_date, function( $query ) use( $from_date ) {
                $query->where( 'date', '>=', $from_date );
            })
            ->orderBy( 'date' )
            ->get();

        // attempt to retrieve a backtrack reference
        // defaults to our trusted 0 reset otherwise
        $backtrack = clone $reset_obj;
        if( $from_date ) {
            $bt = DB::table( $table_data['table_name'] )
                ->where( 'province', '=', $province )
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
                    // set it to backtrack value unless backtrack is also null
                    if( !is_null($backtrack->{$tt_attr}) ) {
                        $update_arr[ $tt_attr ] = $backtrack->{$tt_attr};
                        $report->{$tt_attr} = $update_arr[ $tt_attr ];
                    }
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
            DB::table( $table_data['table_name'] )
                ->where( 'id', '=', $report->id )
                ->update( $update_arr );

            $bar->advance();
        }

        $bar->finish();
        $this->line("");
        $this->line(" Calculations complete >>");

    }
}
