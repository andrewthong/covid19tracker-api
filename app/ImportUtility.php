<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Common;
use App\Utility;
use App\Option;

use App\HrReport;
use App\HealthRegion;
use App\ProcessQueue;

class ImportUtility extends Model
{

    const env_prefix = 'IMPORT_';

    const timestamp_option = 'import_utility_last_timestamp';

    const required_keys = [
        'region',
        'sub_region_1',
        'date',
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
     * helper to set timestamp
     */
    private static function setTimestamp( $timestamp ) {
        Option::set( self::timestamp_option, $timestamp );
    }

    /**
     * helper to load designated meta.json
     */
    private static function getMeta() {
        $env_key = "META_FILE_PATH";
        if(self::isEnabled($env_key)) {
            // get contents of file
            $file_path = self::getEnv($env_key);
            $content = file_get_contents($file_path);
            # convert json to array
            $output = json_decode($content, true);
            return $output;
        } else {
            return [];
        }
    }

    /**
     * helper to update meta information
     */
    private static function updateMeta() {
       $meta = self::getMeta();
       self::setTimestamp( $meta['update_time'] ); 
    }

    /**
     * helper to parse raw csv into array of arrays
     * based off: https://www.php.net/manual/en/function.str-getcsv.php#117692
     */
    private static function parseCsv( $raw_data ) {
        $csv = array_map( 'str_getcsv', $raw_data );
        $headers = array_shift($csv);
        array_walk($csv, function(&$a) use ($headers) {
            # if length differs, create empty
            if( count($a) !== count($headers) ) {
                $a = [];
            } else {
                $a = array_combine($headers, $a);
            }
        });
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

    /**
     * helper to check if diff file exists
     */
    private static function hasDiffFile() {
        $env_key = "DIFF_FILE_PATH";
        if(self::isEnabled($env_key)) {
            $diff_file = self::getEnv($env_key);
            return file_exists($diff_file);
        }
        return false;
    }

    /**
     * helper to delete diff file (informs script we are done)
     */
    private static function deleteDiffFile() {
        // delete diff file
        $diff_file = self::getEnv("DIFF_FILE_PATH");
        unlink($diff_file);
    }

    /**
     * primary function to procss diff file and update database
     */
    private static function processDiffFile() {

        // retrieve diff file
        $diff_file_path = self::getEnv("DIFF_FILE_PATH");
        // expected output is a csv file
        $data = file( $diff_file_path );
        $csv = self::parseCsv($data);

        $response = [
            'rows' => count($csv),
            'created' => 0,
            'updated' => 0,
            'time_start' => microtime(true),
        ];

        $queue_dict = [];

        // it's looping time
        foreach( $csv as $row ) {
            // validate row
            if( count( self::validateRow( self::required_keys, $row ) ) > 0 ) {
                continue;
            }
            // check if exists
            $updated = false;
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
                $updated = true;
                $response['created']++;
            }
            $cases_value = (int)$row['cases'];
            $fatalities_value = (int)$row['fatalities'];
            // set values; only if differs
            if( $cases_value !== $entry->cases ) {
                $entry->cases = $cases_value;
                $updated = true;
            }
            if( $fatalities_value !== $entry->fatalities ) {
                $entry->fatalities = $fatalities_value;
                $updated = true;
            }
            // count updated only if exists
            if ($updated && $entry->exists) {
                $response['updated']++;
            }
            if ($updated) {
                $entry->save();
            }
            // update queue if necessary
            if( isset( $queue_dict[$row['region']] ) ) {
                # if date is earlier, update
                if( $row['date'] > $queue_dict[$row['region']] ) {
                    $queue_dict[$row['region']] = $row['date'];
                }
            } else {
                $queue_dict[$row['region']] = $row['date'];
            }
        }

        // add queue
        foreach( $queue_dict as $region => $date ) {
            ProcessQueue::lineUp($region, $date);
        }

        $response['time_end'] = microtime(true);

        Utility::log( 'ImportUtility', 'Diff processed', $response );
        return $response;
        
    }

    /**
     * process
     */
    public static function process() {

        if(!self::isenabled()) {
            return false;
        }

        // check if diff file exists
        if( self::hasDiffFile() ) {

            // process diff file
            $result = self::processDiffFile();

            // load and update meta information
            self::updateMeta();

            // wrap up
            self::deleteDiffFile();

            return $result;

        }

        return "No diff file found";
    }

}