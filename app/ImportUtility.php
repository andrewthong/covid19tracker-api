<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Common;
use App\Utility;
use App\Option;

use App\HrReport;
use App\HealthRegion;

class ImportUtility extends Model
{

    const env_prefix = 'IMPORT_';

    const timestamp_option = 'import_utility_last_timestamp';

    const cases_required_keys = [
        'region',
        'sub_region_1',
        'date',
        'value',
        'value_daily',
    ];

    /**
     * helper to check if import utility (or related key) is enabled
     * 
     */
    public static function isEnabled( $key = false ) {
        if( !$key ) return env('IMPORT_ENABLED');
        // global enable/disable
        if( !env('IMPORT_ENABLED') ) {
            return false;
        }
        return env(self::env_prefix.$key) !== null;
    }

    private static function getEnv( $key ) {
        return env(self::env_prefix.$key);
    }

    /**
     * helper to determine if value has since changed
     */
    private static function hasNewUpdate( $current_import_value ) {
        $last_import_value = Option::get( self::timestamp_option );
        return $last_import_value !== $current_import_value;
    }
    
    /**
     * helper to get timestamp from source
     */
    private static function getTimestamp() {
        $env_key = "TIMESTAMP_URL";
        if(self::isEnabled($env_key)) {
            // get contents of URL
            $url = self::getEnv($env_key);
            $value = file_get_contents($url);
            $value = str_replace(array("\r", "\n"), '', $value);
            return $value;
        }
    }

    /**
     * helper to set timestamp
     */
    private static function setTimestamp( $timestamp ) {
        Option::set( self::timestamp_option, $timestamp );
    }

    /**
     * helper to parse raw csv into array of arrays
     * based off: https://www.php.net/manual/en/function.str-getcsv.php#117692
     */
    private static function parseCsv( $raw_data ) {
        $csv = array_map( 'str_getcsv', $raw_data );
        array_walk($csv, function(&$a) use ($csv) {
          $a = array_combine($csv[0], $a);
        });
        array_shift($csv);
        return $csv;
    }

    /**
     * helper to validate all keys exist in the row (array)
     * based on: https://stackoverflow.com/a/24849684
     * returns array of $required keys missing in the $row
    */
    private static function validateRow( $required, $row ) {
        return array_diff_key( array_flip( $required ), $row );
    }

    public static function processCases() {
        $env_key = "CASES_URL";
        if(!self::isenabled($env_key)) {
            return false;
        }
        
        // // timestamp gate
        // $timestamp = self::getTimestamp();
        // // compare with saved timestamp
        // if(!self::hasNewUpdate($timestamp)) {
        //     return false;
        // }

        // retrieve cases
        $url = self::getEnv($env_key);
        // expected output is a csv file
        $data = file($url);
        $csv = self::parseCsv($data);

        $response = [
            'rows' => count($csv),
            'created' => 0,
            'updated' => 0,
        ];

        // it's looping time
        foreach($csv as $row) {
            // validate row
            if( count( self::validateRow( self::cases_required_keys, $row ) ) > 0 ) {
                continue;
            }
            // check if exists
            $entry = HrReport::firstOrNew([
                'hr_uid' => $row['sub_region_1'],
                'date' => $row['date'],
            ]);
            // if brand new, verify that hr_uid exists
            if( !$entry->exists ) {
                $hr = HealthRegion::find($row['sub_region_1']);
                if(!$hr) {
                    Utility::log( 'ImportUtility', 'Unrecognized HR UID', $row['sub_region_1'], $row['region'] );
                    continue;
                }
                $response['created']++;
            }
            $cases_value = (int)$row['value'];
            // set values; only if differs
            if( $cases_value !== $entry->cases ) {
                $entry->cases = $row['value'];
                // count updated only if exists
                if ($entry->exists) {
                    $response['updated']++;
                }
                $entry->save();
            }
        }

        // save timestamp
        self::setTimestamp( $timestamp );

        return $response;
        
    }

}