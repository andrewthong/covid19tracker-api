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

}