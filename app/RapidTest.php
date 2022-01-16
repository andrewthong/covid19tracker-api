<?php

namespace App;

use Carbon;

// mongoDB
use Jenssegers\Mongodb\Eloquent\Model;

class RapidTest extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'rapid_tests';

    protected $dates = ['test_date'];

    protected $fillable = [
        'age',
        'postal_code',
        'test_date',
        'test_result',
        'ip',
        '_q',
    ];

    const report_status_key = '_q';

    /**
     * quick helper to return report status key
     */
    static function reportStatusKey() {
        return static::report_status_key;
    }

    /**
     * available values for test result
     */
    static function getTestResultsTypes() {
        return [
            'positive',
            'negative',
            'invalid result'
        ];
    }

    /**
     * helper function to ensure test dates are in valid range
     * returns false if valid
     * returns error message if not valid
     */
    static function isTestDateInvalid($test_date) {

        // earliest test date supported
        $min_date = env('RAPID_TESTS_START_DATE', '2021-12-01');
        $min_u = strtotime($min_date);

        // latest valid date, using St Johns as reference
        $max_u = Carbon\Carbon::now('America/St_Johns')->timestamp;
        $max_date = date('Y-m-d', $max_u);

        $the_date = strtotime($test_date);

        if( $the_date < $min_u ) {
            return "Test date cannot be before {$min_date}";
        }
        if( $the_date > $max_u ) {
            return "Test date cannot be after {$max_date}";
        }

        return false;
    }

}
