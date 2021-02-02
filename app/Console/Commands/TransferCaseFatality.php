<?php

/**
 * 2021-02 transition to reports for main reporting number
 * transfers these stats from processed_reports to reports
 * - total_cases 
 * - total_fatalities
 * (intended to be a temporary command)
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\ProcessedReport;
use App\Report;

class TransferCaseFatality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfer:report_cf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer case/fatality from process_report to report';

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
        // get processed_reports
        $processed_reports = ProcessedReport::all();

        // [artisan]
        $bar = $this->output->createProgressBar( count($processed_reports) );
        $bar->start();

        // loop through each processed report
        foreach( $processed_reports as $pr ) {
            if( $pr->province && $pr->date ) {
                $affected = DB::table('reports')
                    ->where([
                        ['province', '=', $pr->province],
                        ['date', '=', $pr->date]
                    ])
                    ->update([
                        'cases' => $pr->total_cases,
                        'fatalities' => $pr->total_fatalities,
                    ]);
                if( $affected === 0 ) {
                    // update won't count if values are the same
                    // only trigger warning when there is no corresponding row
                    $check = DB::table('reports')
                        ->where([
                            ['province', '=', $pr->province],
                            ['date', '=', $pr->date]
                        ])
                        ->get();
                    if( $check->isEmpty() ) {
                        $this->line(" # No matching record in ID {$pr->id}");
                    }
                }
            } else {
                $this->line(" # Invalid province/date in ID {$pr->id}");
            }
            $bar->advance();
        }

        return 0;
    }
}
