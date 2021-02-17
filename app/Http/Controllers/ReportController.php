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

    
    /*
        produces province report
            $province: optional province code (defaults to all provinces)
    */
    public function generateProvince( Request $request, $province = null ) {
        return $this->generateReport( $request, 'province', $province );
    }

    
    /*
        produces health region report based on province (grouped)
            $hr_uid: optional health region uid (defaults to all health regions)
    */
    public function generateHealthRegion( Request $request, $hr_uid = null ) {
        return $this->generateReport( $request, 'healthregion', $hr_uid );
    }
    
    /*
        produces health region report based on province (grouped)
            $province: required province code
    */
    public function generateProvinceHealthRegion( Request $request, $province ) {
        // set this value to be read by generate function
        $request->merge( ['group_by_province' => true] );
        return $this->generateReport( $request, 'healthregion', $province );
    }
    
    /*
        produces report with daily and cumulative totals for key attributes
            $type: optional province(default)|healthregion
            $location: optional province code or hr_uid
    */
    public function generateReport( Request $request, $type = 'province', $location = null ) {

        // cache
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $type, $location) {

            // setup; get attributes based on type
            $core_attrs = Common::attributes( null, $type );
            // TODO: migrate to a config
            $date_col = 'date';
            $change_prefix = 'change_';
            $total_prefix = 'total_';
            $reset_value = 0;
            // used for grouping by province
            $hr_uids = [];

            // base arrays to build various pieces of the db query with
            $select_core = [$date_col];
            $where_core = [];

            // default group by
            $groupby_core = [$date_col];

            // default order by
            $orderby_core = [$date_col];

            // query core modifiers
            foreach( [$change_prefix, $total_prefix] as $prefix ) {
                foreach( $core_attrs as $attr ) {
                    $select_core[] = "SUM({$prefix}{$attr}) AS {$prefix}{$attr}";
                }
            }

            // base (province)
            $location_col = 'province';
            $processed_table = 'processed_reports';

            // health region
            if( $type === 'healthregion' ) {
                $location_col = 'hr_uid';
                $processed_table = 'processed_hr_reports';
                // check if grouping by province
                if( $request->group_by_province ) {
                    // get hr_uids of the province, then glue for IN stmt
                    $hr_uids = implode( ',', Common::getHealthRegionCodes($location) );
                    // add location where as IN stmt
                    $where_core[] = "{$location_col} IN ({$hr_uids})";
                    // additional hr_uid to select, group and order to include in query
                    $select_core[] = $location_col;
                    $groupby_core[] = $location_col;
                    $orderby_core[] = $location_col;
                }
            }

            // specific province/health region request
            // if where_core is not empty, then it has hr_uid IN for location
            if( $location && empty($where_core) ) {
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

            // prepare GROUP BY
            $groupby_stmt = implode( ',', $groupby_core );

            // prepare ORDER BY
            $orderby_stmt = implode( ',', $orderby_core );

            $result = DB::select("
                SELECT
                    {$select_stmt}
                FROM
                    {$processed_table}
                {$where_stmt}
                GROUP BY
                    {$groupby_stmt}
                ORDER BY
                    {$orderby_stmt}
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

            // response
            $response = [];

            // specific response handling for grouped health regions
            if( $request->group_by_province ) {
                $response['province'] = $location;
                $response['hr_uid'] = 'All';
                $response['last_updated'] = Common::getLastUpdated( $location );
                // reformat data
                $grouped_data = [];
                foreach( $data as $row ) {
                    $key = $row['hr_uid'];
                    // create blank entry for each unique hr_uid
                    if( !array_key_exists($key, $grouped_data) ) {
                        $grouped_data[$key] = [];
                    }
                    // remove hr_uid
                    unset($row['hr_uid']);
                    // append to data
                    $grouped_data[$key][] = $row;
                } 
                $response['data'] = $grouped_data;
            } else {
                $response[$location_col] = $location ? $location : 'All';
                $response['last_updated'] = Common::getLastUpdated( $location && $type === 'province' ? $location : $type );
                $response['data'] = $data;
            }

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
            
        });//cache closure

        return $value;
    }

}
