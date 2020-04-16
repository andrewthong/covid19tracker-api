<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Province;
use App\Cases;
use App\Fatality;

class ReportController extends Controller
{
    
    /*
        produces report with daily and cumulative totals for key attributes
    */
    public function generate( Request $request, $province = null ) {

        // TODO: migrate to a config
        $core_attrs = [
            'tests', 'cases', 'hospitalizations', 'criticals', 'fatalities', 'recoveries'
        ];
        $cumu_attrs = [
            'tests', 'cases', 'fatalities', 'recoveries'
        ];
        $ct_prefix = "_ct"; // simple prefixes
        $cu_prefix = "_cu";

        // check for province request
        $subwhere_core = [];
        if( $province ) {
            $subwhere_core[] = "province = '{$province}'";
        }

        // full config
        $cumulative = false;
        if( $request->full ) {
            $cumulative = true;
        }

        // date
        if( $request->date ) {
            // single date does not need cumulative
            $cumulative = false;
            $subwhere_core[] = "`date` = '{$request->date}'";
        }

        // stat
        // return on single statistic as defined
        if( $request->stat && in_array( $request->stat, $core_attrs ) ) {
            $core_attrs = [$request->stat];
        }

        // start building our query
        $select_core = [];
        $subselect_core = [];

        // loop through each
        foreach( $core_attrs as $attr ) {
            // internal column name
            $ct = "{$attr}{$ct_prefix}";
            // count prefix: depends if attribute supports cumulative
            $ct_postfix = "new_";
            if( !in_array($attr, $cumu_attrs) ) $ct_postfix = "total_";
            // add count to select statements
            //   tests AS daily_tests
            $select_core[] = "{$ct} as {$ct_postfix}{$attr}";
            //   SUM(tests) AS tests_ct
            $subselect_core[] = "SUM({$attr}) AS {$ct}";

            // cumulative mode
            if( $cumulative && in_array($attr, $cumu_attrs) ) {
                $cu = "@{$attr}{$cu_prefix}";
                // add cumulative to select statement
                //   @tests_cu := @tests_cu + IFNULL(tests_ct, 0)
                $select_core[] = "{$cu}:={$cu} + IFNULL({$ct}, 0) as cumu_{$attr}";
                //   @tests_cu:=0
                $subselect_core[] = "{$cu}:=0";
            }
        }

        // prepare SELECT
        $select_stmt = implode(",", $select_core);
        $subselect_stmt = implode(",", $subselect_core);
        $subwhere_stmt = "";
        if( $subwhere_core ) {
            $subwhere_stmt = "WHERE " . implode(" AND ", $subwhere_core);
        }

        $data = DB::select("
            SELECT
                r.date,
                {$select_stmt}
            FROM (
                SELECT
                    `date`,
                    {$subselect_stmt}
                FROM reports
                {$subwhere_stmt}
                GROUP BY `date`
            ) AS r
            ORDER BY r.date
        ");

        $response = [
            'province' => $province ? $province : 'All',
            'data' => $data,
        ];

        return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function generateFull( Request $request, $province = null ) {
        $request->merge(['full' => true]);
        return $this->generate( $request, $province );
    }

}
