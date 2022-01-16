<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Common;
use App\Option;

use App\RapidTest;
use App\RapidTestReport;

class RapidTestReportController extends Controller
{
    
    /**
     * summary of collected rapid tests from reports
     * using reports so that only processed submissions are counted
     */
    public function summary() {
        // cache
        // $cache_key = \Request::getRequestUri();
        // $value = Cache::rememberForever( $cache_key, function() {

            $response = [
                'test_results' => [],
                'test_dates' => [],
            ];

            $rapid_test_reports = RapidTestReport::all();

            $results = RapidTest::getTestResultsTypes( true );
            $total = 0;
            foreach( $results as $type ) {
                $sum = $rapid_test_reports->sum( $type );
                $response['test_results'][$type] = $sum;
                $total += $sum;
            }
            
            $response['total'] = $total;

            $response['test_dates']['earliest'] = RapidTestReport::orderBy('date', 'asc')->first()->date;
            $response['test_dates']['latest'] = RapidTestReport::orderBy('date', 'desc')->first()->date;

            return response()->json($response);

        // });//cache closure
    }

    /**
     * produces report with all rapid test results
     */
    public function generateReport( Request $request, $location = null ) {

        // cache
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request,$location) {

            // setup
            $core_attrs = RapidTest::getTestResultsTypes(true);
            $reset_value = 0;

            $where_core = [];

            $processed_table = 'rt_reports';

            // location (province)
            if( $location ) {
                $where_core[] = "`province` = '{$location}'";
            }

            // date
            if( $request->date ) {
                $where_core[] = "`date` = '{$request->date}'";
            }
            // date range (if date is not provided)
            else if( $request->after ) {
                $where_core[] = "`date` >= '{$request->after}'";
                // before defaults to today
                $date_before = date('Y-m-d');
                if( $request->before ) {
                    $date_before = $request->before;
                }
                $where_core[] = "`date` <= '{$date_before}'";
            }

            // stat
            // return on single statistic as defined
            if( $request->stat &&
                in_array( $request->stat, $core_attrs )
            ) {
                $core_attrs = [$request->stat];
            }

            // build out select list
            $select_core = ['date'];
            foreach( $core_attrs as $attr ) {
                $select_core[] = "SUM({$attr}) AS {$attr}";
            }
            
            // prepare SELECT
            $select_stmt = implode(",", $select_core);
            $where_stmt = "";
            if( $where_core ) {
                $where_stmt = "WHERE " . implode(" AND ", $where_core);
            }

            $result = DB::select("
                SELECT
                    {$select_stmt}
                FROM
                    {$processed_table}
                {$where_stmt}
                GROUP BY
                    `date`
                ORDER BY
                    `date`
            ");

            // convert DB::select to a basic array
            $data = json_decode(json_encode($result), true);

            // timestamp
            $last_run = Option::get('rapid_test_last_processed');

            $response = [
                'province' => $location ? $location : 'All',
                'last_updated' => $last_run,
                'data' => $data,
            ];

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
            
        });//cache closure

        return $value;
    }

}
