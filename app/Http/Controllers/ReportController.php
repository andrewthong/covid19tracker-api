<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Province;
use App\Cases;
use App\Fatality;
use App\ProcessedReport;

class ReportController extends Controller
{

    /**
     * summary takes latest reports for each province and aggregates
     *  - $split if true, will not aggregate
     */
    public function summary( $split = false ) {
        // setup
        $core_attrs = Common::attributes();
        $change_prefix = 'change_';
        $total_prefix = 'total_';

        // preparing SQL query
        $select_core = [];
        $date_select = "MAX(r1.date) AS latest_date";
        $stat_select = 'SUM(r1.%1$s) AS %1$s';

        // $split modifiers, we no longer need to group
        if( $split ) {
            $select_core[] = "r1.province";
            $date_select = "r1.date";
            $stat_select = 'r1.%1$s';
        }

        $select_core[] = $date_select;
        foreach( [$change_prefix, $total_prefix] as $prefix ) {
            foreach( $core_attrs as $attr ) {
                // $select_core[] = "SUM(r1.{$prefix}{$attr}) AS {$prefix}{$attr}";
                $select_core[] = sprintf( $stat_select, "{$prefix}{$attr}" );
            }
        }

        $select_stmt = implode( ",", $select_core );

        $report = DB::select("
            SELECT
                {$select_stmt}
            FROM
                processed_reports AS r1
            LEFT JOIN
                processed_reports AS r2
                ON r1.province = r2.province
                AND r1.date < r2.date
                WHERE r2.province IS NULL
        ");

        return [
            'data' =>  $report
        ];
    }
    
    /*
        produces report with daily and cumulative totals for key attributes
    */
    public function generate( Request $request, $province = null ) {

        // setup
        $core_attrs = Common::attributes();
        $change_attrs = Common::attributes('change');
        $total_attrs = Common::attributes('total');
        // TODO: migrate to a config
        $change_prefix = 'change_';
        $total_prefix = 'total_';
        $reset_value = 0;

        $where_core = [];

        // query core modifiers
        foreach( [$change_prefix, $total_prefix] as $prefix ) {
            foreach( $core_attrs as $attr ) {
                $select_core[] = "SUM({$prefix}{$attr}) AS {$prefix}{$attr}";
            }
        }

        // check for province request
        if( $province ) {
            $where_core[] = "province = '{$province}'";
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
                processed_reports
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

        $response = [
            'province' => $province ? $province : 'All',
            'data' => $data,
        ];

        return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

}
