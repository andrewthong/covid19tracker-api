<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DateTime;
use Illuminate\Support\Facades\Artisan;

use App\Utility;
use App\Option;

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

    /**
     * returns all queue items that have not been processed
     */
    public static function getLine() {
        return self::where([
            'processed' => false,
        ])->get();
    }

    /**
     * processes all items in the queue
     */
    static function process() {
        $processed_ids = [];
        // retrieve items awaiting processing
        $items = self::getLine();
        if( count($items) ) {
            // loop
            foreach( $items as $item ) {
                $params = [
                    '--province' => $item->province,
                    '--date' => $item->date,
                    '--noclear' => true,
                    '--nolast' => true
                ];
                // run processing for province
                $exit_code = Artisan::call('report:process', $params);
                // run processing for each health region
                $exit_code_hr = Artisan::call('report:processhr', $params);
                // store id to update later
                $processed_ids[] = $item->id;
            }
            // mark queue items as processed
            self::whereIn('id', $processed_ids)
                ->update([
                    'processed' => true
                ]);
            // clear cache
            Utility::clearCache();
            // modify global last updated
            Option::set( 'report_last_processed', date('Y-m-d H:i:s') );
            // add log entry
            Utility::log('process_queue', count($items), $processed_ids);
        }

        return [
            'processed' => $processed_ids
        ];
    }
}
