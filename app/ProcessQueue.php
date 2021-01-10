<?php

namespace App;

use DateTime;

use Illuminate\Database\Eloquent\Model;

class ProcessQueue extends Model
{
    protected $table = 'process_queue';

    protected $fillable = [
        'province',
        'date',
        'processed',
    ];

    public $timestamps = true;

    /**
     * adds a province-date item to the process queue stack
     *   province_code: a valid province code
     *   date: Y-m-d
     */
    public static function lineUp( $province_code, $date ) {
        // check if there is a province waiting
        $existing = self::where([
            'province' => $province_code,
            'processed' => false,
        ])->first();
        if( $existing ) {
            // compare dates; update if older
            if( new DateTime($date) < new DateTime($existing->date) ) {
                $existing->date = $date;
                $existing->save();
                return "{$province_code} queue date updated";
            }
            // nothing needed
            return "{$province_code} already in queue";
        } else {
            // add to queue stack
            self::create([
                'province' => $province_code,
                'date' => $date,
            ]);
            return "{$province_code} added to queue";
        }
    }
}
