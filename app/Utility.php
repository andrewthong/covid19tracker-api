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

}