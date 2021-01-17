<?php

namespace App;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Common;
use App\Province;
use App\processQueue;

class Utility
{

    static function clearCache( $key = null ) {
        $response = [
            'all' => $key ? false : true,
            'key' => $key,
        ];
        if( $key ) {
            Cache::forget( $key );
        } else {
            Cache::flush();
        }
        return $response;
    }

    static function processQueue() {
        // retrieve items awaiting processing
        $items = ProcessQueue::getLine();
        if( $items ) {
            // loop
            foreach( $items as $item ) {
                $exit_code = Artisan::call('report:process', [
                    '--province' => $item->province,
                    '--date' => $item->date
                ]);
                $exit_code_hr = Artisan::call('report:processhr', [
                    '--province' => $item->province,
                    '--date' => $item->date
                ]);
            }
        }
    }

    /**
     * helper to send a log/info call
     * $items: mixed value pieces to include in the log entry
     */
    static function log(...$items) {
        $parts = [];
        foreach( $items as $item ) {
            try {
                $parts[] = json_encode( $item );
            } catch(Exception $e) {
            }
        }
        $message = implode( "\t", $parts );
        Log::info( $message );
    }

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

    /**
     * rudimentary helper to apply hr_uid data to cases
     */
    public static function updateHrUid( $object_type ) {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        // some validation
        if( !in_array($object_type, ['cases', 'fatalities']) ) {
            $out->writeln( "Invalid Object" );
            exit();
        }
        // load JSON file
        $json_file = base_path() . '/database/seeds/tmp/'.$object_type.'_hr_uid.json';
        $json_string = file_get_contents( $json_file );
        $hr_uids = json_decode($json_string, true);
        if( $hr_uids !== FALSE ) {
            foreach( $hr_uids as $hr_uid => $ids ) {
                $case_hr_uid = (int) $hr_uid;
                // split into chunks
                $chunks = array_chunk($ids, 100);
                $out->writeln( "HR UID: ".$case_hr_uid );
                $out->write("> Chunk ");
                foreach( $chunks as $chunk_no => $chunk ) {
                    $out->write( ($chunk_no+1)." " );
                    DB::table( $object_type )
                        ->whereIn('id', $chunk)
                        ->update(['hr_uid' => $case_hr_uid]);
                }
                $out->writeln("");
            }
        } else {
            $out->writeln( "Invalid JSON" );
        }
        return true;
    }

    /**
     * helper to get case and fatality for a specific date based on province or health region
     *   date (YYYY-MM-DD)
     *   location ( the provinde or hr_uid )
     *   location_col ( province, or hr_uid )
     *   operand ( '=' for change, '<=' for total )
     */
    public static function countCaseFatality($date, $location, $location_col = 'province', $operand = '=') {

        if( $location_col !== 'hr_uid' ) {
            $location = "'{$location}'";
        }

        $where_core = [
            "`date`{$operand}'{$date}'",
            "`{$location_col}`={$location}",
        ];

        $where_stmt = "WHERE ".implode(" AND ", $where_core);

        $records = DB::select("
            SELECT
                COUNT(c_id) as cases,
                COUNT(f_id) as fatalities
            FROM (
                SELECT
                    id AS c_id,
                    null AS f_id
                FROM 
                    `cases`
                {$where_stmt}
                UNION
                SELECT
                    null as c_id,
                    id AS f_id
                FROM
                    `fatalities`
                {$where_stmt}
            ) AS un
        ");

        return $records[0];
    }

}