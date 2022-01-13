<?php

namespace App;

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
    ];

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

}
