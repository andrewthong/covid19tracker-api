<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Common;
use App\Utility;
use App\Option;

use App\RapidTest;
use App\RapidTestReport;
use App\PostalDistrict;

class ProcessRtReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:processrt
                            {--limit= : max amount}
                            {--noclear}
                            {--nolast}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes rapid test results into a report format';

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
        // options support
        $options = $this->options();

        // setup
        $key = RapidTest::report_status_key;

        // canadian postal code first letter to province code
        $postal_dict = PostalDistrict::dictionary();

        // flag to check if we need to clear cache 
        $updated = false;

        // max amount of docs to pull
        $limit = intval($this->option('limit'));
        if( !$limit ) {
            $limit = 1000;
        }

        $this->line("");

        $this->line(" # <fg=black;bg=white>Rapid test data processing utility</>");
        $this->line(" # COVID-19 Tracker API v1.0 #");

        $this->line("");
        
        $this->line(" >> Fetching test results... (limit: {$limit})");

        // grab unprocessed test results
        $test_results = RapidTest::where($key, null)
            ->limit($limit)
            ->get();
        
        $this->line(" >> {$test_results->count()} new test results found");

        $bar = $this->output->createProgressBar( count($test_results) );
        $bar->start();

        // loop through test results
        foreach ($test_results as $test) {
            $province = null;
            $date = null;
            // verify forward sortation area (via first letter)
            $first_letter = substr($test->postal_code, 0, 1);
            if( isset($postal_dict[$first_letter]) ) {
                $province = $postal_dict[$first_letter];
            }
            // Nunavut and Northwest Territories share X
            // defaults to NU but we can detect NT
            if( $first_letter == 'X' ) {
                if( in_array($test->postal_code, ['X0E', 'X0G']) ) {
                    $province = 'NT';
                }
            }
            // verify date
            if( !RapidTest::isTestDateInvalid($test->test_date) ) {
                $date = $test->test_date;
            }
            // continue only if province and date are valid
            if( $province && $date ) {

                // get report
                $report = RapidTestReport::updateOrCreate(
                    ['province' => $province, 'date' => $date]
                );

                // increment appropriate test result
                $increment_col = $test->test_result;
                // fallback "invalid result" and other test results
                if( !in_array($increment_col, ['positive', 'negative']) ) {
                    $increment_col = 'invalid';
                }
                $report[$increment_col] += 1;
                $report->save();

                // update processing status
                $test->update([$key => 'processed']);

            } else {
                // update processing status
                $test->update([$key => 'ignored']);
            }

            $bar->advance();
        }

        $bar->finish();

        $this->line('');
        $this->line(" <fg=green;bg=black>Processing complete.</>");
        $this->line('');
        $this->line(' Have a nice day ツ');
        $this->line('');

        return 0;
    }
}
