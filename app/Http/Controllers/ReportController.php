<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Common;
use App\Option;
use App\Province;
use App\ProcessedReport;

class ReportController extends Controller
{

    public function summaryProvince( $split = false ) {
        return $this->summary( $split );
    }

    public function summaryHealthRegion( $split = true ) {
        return $this->summary( $split, 'healthregion' );
    }

    /**
     * summary takes latest reports for each province and aggregates
     *  - $split if true, will not aggregate
     *  - $type province or healthregion
     */
    public function summary( $split = false, $type = 'province' ) {

        // cache
        $cache_key = \Request::getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($split, $type) {

            // setup
            $core_attrs = Common::attributes( null, $type );
            $change_prefix = 'change_';
            $total_prefix = 'total_';

            $location_col = 'province';
            $processed_table = 'processed_reports';
            $location_codes = [];

            if( $type === 'healthregion' ) {
                $location_col = 'hr_uid';
                $processed_table = 'processed_hr_reports';
                $location_codes = Common::getHealthRegionCodes();
            } else {
                $location_codes = Common::getProvinceCodes();
            }

            // meta
            $last_run = Common::getLastUpdated( $type );

            // preparing SQL query
            $select_core = [];
            $date_select = "MAX(date) AS latest_date";
            $stat_select = 'SUM(%1$s) AS %1$s';

            // $split modifiers, we no longer need to group
            if( $split ) {
                $select_core[] = $location_col;
                $date_select = "date";
                $stat_select = '%1$s';
            }

            $select_core[] = $date_select;
            foreach( [$change_prefix, $total_prefix] as $prefix ) {
                foreach( $core_attrs as $attr ) {
                    // $select_core[] = "SUM({$prefix}{$attr}) AS {$prefix}{$attr}";
                    $select_core[] = sprintf( $stat_select, "{$prefix}{$attr}" );
                }
            }

            $subquery_core = [];
            $subquery_stmt = '';
            $query = '';

            // 2020-12-22: subquery is bogging down in health_regions
            if( $type === 'healthregion' ) {
                $select_core = array_map(function($value) { return 't1.'.$value; }, $select_core);
                $select_stmt = implode( ",", $select_core );
                $query = "
                    SELECT {$select_stmt} from {$processed_table} t1 
                    JOIN (SELECT hr_uid, MAX(`date`) as latest_date from {$processed_table} group by `hr_uid`) t2 
                    ON t1.hr_uid = t2.hr_uid AND t1.date = t2.latest_date
                ";
            } else {
                $select_stmt = implode( ",", $select_core );
                foreach( $location_codes as $lc ) {
                    $subquery_core[] = "(
                        SELECT *
                        FROM {$processed_table}
                        WHERE
                            {$location_col}='{$lc}'
                        ORDER BY `date` DESC
                        LIMIT 1
                    )";
                }
                $subquery_stmt = implode( " UNION ", $subquery_core );
                $query = "
                    SELECT
                        {$select_stmt}
                    FROM (
                        {$subquery_stmt}
                    ) pr
                ";
            }

            $report = DB::select($query);

            $response = [
                'data' =>  $report,
                'last_updated' => $last_run,
            ];

            // return to be stored in
            return $response;
            
        });//cache closure

        return $value;
    }

    public function generateProvince( Request $request, $province = null ) {
        return $this->generateReport( $request, 'province', $province );
    }

    public function generateHealthRegion( Request $request, $hr_uid = null ) {
        return $this->generateReport( $request, 'healthregion', $hr_uid );
    }
    
    /*
        produces report with daily and cumulative totals for key attributes
    */
    public function generateReport( Request $request, $type = 'province', $location = null ) {

        // cache
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $type, $location) {

            // setup
            $core_attrs = Common::attributes( null, $type );
            // TODO: migrate to a config
            $change_prefix = 'change_';
            $total_prefix = 'total_';
            $reset_value = 0;

            $where_core = [];

            // base (province)
            $location_col = 'province';
            $processed_table = 'processed_reports';

            if( $type === 'healthregion' ) {
                $location_col = 'hr_uid';
                $processed_table = 'processed_hr_reports';
            }

            // check for province request
            if( $location ) {
                $where_core[] = "{$location_col} = '{$location}'";
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
            if( $request->stat && in_array( $request->stat, $core_attrs ) ) {
                $core_attrs = [$request->stat];
            }

            // build out select list
            $select_core = ['date'];
            foreach( [$change_prefix, $total_prefix] as $prefix ) {
                foreach( $core_attrs as $attr ) {
                    $select_core[] = "SUM({$prefix}{$attr}) AS {$prefix}{$attr}";
                }
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

            // fill dates (useful for charting)
            if( $request->fill_dates ) {
                // prepare a reset array; all change_{stat} must be null
                $reset_arr = ['fill' => 1];
                foreach( $core_attrs as $attr ) {
                    $reset_arr["{$change_prefix}{$attr}"] = null;
                }
                $data = Common::fillMissingDates( $data, $reset_arr );
            }

            // timestamp
            $last_run = Common::getLastUpdated( $location && $type === 'province' ? $location : $type );

            $response = [
                $location_col => $location ? $location : 'All',
                'last_updated' => $last_run,
                'data' => $data,
            ];

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
            
        });//cache closure

        return $value;
    }

    public function generateRecentHealthRegion() {
        // cache (requests not supported)
        $cache_key = 'reports/health-regions/recent';
        $value = Cache::rememberForever( $cache_key, function() {
            
            // setup
            $select_core = array_merge(
                ['date', 'hr_uid'],
                Common::prefixArrayItems( Common::attributes() )
            );

            $table = 'processed_hr_reports';

            // get last 15 days
            $date_from = date('Y-m-d', strtotime('-15 days'));

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
                    `date`, `hr_uid`
            ");

            $last_run = Common::getLastUpdated( 'healthregion' );

            $response = [
                'last_updated' => $last_run,
                'data' => $data,
            ];

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);

        });//cache closure

        return $value;
    }

}
