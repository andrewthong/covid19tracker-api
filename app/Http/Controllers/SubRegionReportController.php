<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Common;
use App\Option;

use App\SubRegion;
use App\SrVaccineReport;

class SubRegionReportController extends Controller
{
    /**
     * get sub region vaccine reports
     * $code: required sub region code
     */
    public function report( Request $request, $code ) {

        // cache
        $cache_key = \Request::getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use( $request, $code ) {

            $table = 'sr_vaccine_reports';

            $select_core = array_merge(
                ['date'],
                SrVaccineReport::statAttrs()
            );

            $where_core = [];

            // [sub-region] code
            $where_core[] = "code = '{$code}'";

            // before and after date
            if( $request->after ) {
                $where_core[] = "`date` >= '{$request->after}'";
            }
            if( $request->before ) {
                $where_core[] = "`date` <= '{$request->before}'";
            }

            // query
            $select_stmt = implode( ",", $select_core );
            $where_stmt = "";
            if( $where_core ) {
                $where_stmt = "WHERE " . implode(" AND ", $where_core);
            }

            $query = "SELECT {$select_stmt} FROM {$table} {$where_stmt} ORDER BY `date` ASC";

            $report = DB::select($query);

            $response = [
                'sub_region' => $code,
                'data' =>  $report,
            ];

            return $response;

        });//cache closure

        return $value;

    }

    public function recentReports() {
        // cache (requests not supported)
        $cache_key = 'reports/sub-regions/recent';
        $value = Cache::rememberForever( $cache_key, function() {
            
            // setup
            $select_core = array_merge(
                ['date', 'code'],
                SrVaccineReport::statAttrs()
            );

            $table = 'sr_vaccine_reports';

            // days to go back
            $days_ago = env('RECENT_REPORT_DAYS', 7);
            $date_from = date('Y-m-d', strtotime("-{$days_ago} days"));

            // prepare SELECT
            $select_stmt = implode(",", $select_core);

            // prepare WHERE
            $where_stmt = "WHERE `date` >= '{$date_from}'";

            // DB query
            $data = DB::select("
                SELECT
                    {$select_stmt}
                FROM
                    {$table}
                {$where_stmt}
                ORDER BY
                    `date`, `code`
            ");

            $last_run = Common::getLastUpdated( 'healthregion' );

            $response = [
                'last_updated' => $last_run,
                'recent_from' => $date_from,
                'data' => $data,
            ];

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);

        });//cache closure

        return $value;
    }
}
