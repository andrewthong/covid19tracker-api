<?php

namespace App;

use Illuminate\Support\Facades\DB;

use App\Common;
use App\Province;

class Utility
{

    /**
     * utility function to convert provinces attribute in defined tables
     * $to_code: if true, converts names to code where applicable
     */
    static function transformProvinces( $to_code = true ) {
        // modular from-to
        $vfrom = 'name';
        $vto = 'code';
        // swap them around (from code to names)
        if( $to_code !== false ) {
            list( $vto, $vfrom ) = array( $vto, $vfrom );
        }
        // get all provinces
        $provinces = Province::all()->toArray();
        $result = array();

        // tables to comb through
        $tables = [
            'cases',
            'fatalities',
        ];
    
        foreach( $tables as $table ) {
            foreach( $provinces as $province ) {
                // run an update statement for each province
                // Eloquent equivalent to
                //   update `cases` set `province` = 'code_or_name' where `province` = 'name_or_code' 
                $affected_rows = DB::table( $table )
                    ->where( 'province', $province[$vfrom] )
                    ->update( ['province' => $province[$vto]] );
                $result[] = array(
                    'from' => $province[$vfrom],
                    'to' => $province[$vto],
                    'affected_rows' => $affected_rows,
                );
            }
        }
        return $result;
    }

    /**
     * this utility function takes counts cases and fatalities to fill the reports
     * needed for reporting unless reports also include cases and fatalities
     * current tracking logs cases and fatalities on an individual level
    */
    static function transferToReports() {

        $provinces = Province::all()->pluck('code');
        
        // count known provinces and records using province code
        $provinces_in = implode( "','", $provinces->toArray() );
        $where_stmt = "WHERE province IN ('{$provinces_in}')";

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
                UNION
                SELECT
                    province,
                    DATE(`date`) AS day,
                    null as c_id,
                    id AS f_id
                FROM
                    `fatalities`
            ) AS un
            {$where_stmt}
            GROUP BY
                day,
                province
            ORDER BY
                day
        ");

        $response = [];

        foreach( $records as $record ) {
            DB::table('reports')
                ->updateOrInsert(
                    [
                        'date' => $record->day,
                        'province' => $record->province
                    ],
                    [
                        'date' => $record->day,
                        'province' => $record->province,
                        'cases' => $record->cases,
                        'fatalities' => $record->fatalities,
                    ]
                );
        }

        return $response;
        
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
    static function processReportsMode( $mode = null ) {

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

    static function test( $mode = null ) {
        $res = DB::table( 'reports' )
            ->where( 'province', '=', 'ON' )
            ->where( 'date', '<', '2020-01-31' )
            ->orderBy( 'date' )
            ->first();

        $comp = (object) ['a' => 'new object'];
        $comp->cases = 10;

        if($res) {
            return $res->cases + $comp->cases;
        } else {
            return false;
        }
    }

    /**
     * generates ProcessedReports
     * 
     */
    static function processReports( $mode = null ) {

        // process change_{stat}s (cases, fatalities)
        // return self::processReportChanges( $mode );
        
        // process total_{stat}s (tests, hospitalizations, criticals, recoveries)
        // return self::processReportTotals( $mode );

        // fill in gaps (change <-> total)
        return self::processReportGaps( $mode );

    }

    /**
     * changes are data that is stored on an individual basis
     * cases and fatalities by default
     * this sub-helper processes these
     */
    static function processReportChanges( $mode = null ) {

        $from_date = self::processReportsMode( $mode );

        // only for registered provinces
        $province_codes = Common::getProvinceCodes();

        $where_core = [];
        
        // only include known provinces
        $provinces_in = implode( "','", $province_codes );
        $where_core[] = "province IN ('{$provinces_in}')";

        // only process records on or after this date
        if( $from_date ) {
            $where_stmt[] = "date >= '{$from_date}'";
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
                UNION
                SELECT
                    province,
                    DATE(`date`) AS day,
                    null as c_id,
                    id AS f_id
                FROM
                    `fatalities`
            ) AS un
            {$where_stmt}
            GROUP BY
                day,
                province
            ORDER BY
                day
        ");

        $response = [];

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
        }
    }

    /**
     * totals are data that is stored in the reports log
     * they are an accumulate total of tracked stats
     * this sub-helper moves these to the processReports table
     */
    static function processReportTotals( $mode = null ) {

        // determine date to run on based on mode
        $from_date = self::processReportsMode( $mode );

        // only for registered provinces
        $province_codes = Common::getProvinceCodes();

        // retrieve reports
        $reports = DB::table( 'reports' )
            ->whereIn( 'province', $province_codes )
            ->when( $from_date, function( $query ) use( $from_date ) {
                $query->where( 'date', '>=', $from_date );
            })
            ->orderBy('date')
            ->get();

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
                        'notes' => $report->notes,
                    ]
                );
        }
    }

    /**
     * this sub-helper runs through process reports and attempts
     * to fill in incomplete change_ and total_ numbers
     */
    static function processReportGaps( $mode = null ) {

        // determine date to run on based on mode
        $from_date = self::processReportsMode( $mode );

        // list of provinces
        $province_codes = Common::getProvinceCodes();

        // core attributes
        $core_attrs = [
            'cases',
            'fatalities',
            'tests',
            'hospitalizations',
            'criticals',
            'recoveries'
        ];
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
                    ->orderBy( 'date' )
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
                    // subtract current total w/ backtrack total
                    $update_arr[ $ch_attr ] =
                          $report->{$tt_attr}
                        - $backtrack->{$tt_attr};
                    $report->{$ch_attr} = $update_arr[ $ch_attr ];
                }
                // update db
                DB::table('processed_reports')
                    ->where( 'id', '=', $report->id )
                    ->update( $update_arr );
                // report is now new backtrack
                $backtrack = clone $report;
            }

        }



        // we require a reverse control when mode !== 'all

    }

}