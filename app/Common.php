<?php

namespace App;

use DateTime;

use App\HealthRegion;
use App\Province;

class Common {
    
    /* 
        generates an array of dates between two days
        $first_date
        $last_date: optional, defaults to (today)
        $format: Y-m-d
    */
    static function getDateArray( $first_date, $last_date = null, $format = 'Y-m-d' )
    {
        // revert to today if no end is provided
        if( is_null( $last_date ) ) {
            $last_date = date('Y-m-d');
        }

        $dates = array();
        // convert to time
        $start_date = strtotime( $first_date );
        $end_date = strtotime( $last_date );

        while( $start_date <= $end_date ) {
            $dates[] = date( $format, $start_date );
            // might not be performat, can review in future
            $start_date = strtotime( '+1 day', $start_date );
        }

        return $dates;
    }

    /**
     * return core attributes
     * optional $t (type)
     *  - change: returns attributes where data source is from change_
     *  - total: returns attributes where data source is from total_
     * optional $p (province)
     *  - true/"province": adds vaccines_distributed
     */
    static function attributes( $t = null, $p = false ) {
        $core_attrs = [
            'cases',
            'fatalities',
            'tests',
            'hospitalizations',
            'criticals',
            'recoveries',
            'vaccinations',
        ];
        // province specific fields
        if( $p === true || $p === 'province' ) {
            $core_attrs[] = 'vaccines_distributed';
            $core_attrs[] = 'vaccinated';
        }
        if( $t === 'change' ) {
            // change_ sourced attributes
            return array_slice( $core_attrs, 0, 2 );
        } elseif( $t === 'total' ) {
            // total_ sourced attributes
            return array_slice( $core_attrs, 2 );
        }
        return $core_attrs;
    }

    /**
     * return an array of province codes
     */
    static function getProvinceCodes( $geo_only = true ) {
        if( $geo_only ) {
            return Province::where('geographic', 1)->pluck('code')->toArray();
        } else {
            return Province::all()->pluck('code')->toArray();
        }
    }

    /**
     * validate province code
     */
    static function isValidProvinceCode( $code, $geo_only = true ) {
        $provinces = self::getProvinceCodes( $geo_only );
        return in_array( $code, $provinces );
    }

    /**
     * return an array of hr_uid
     */
    static function getHealthRegionCodes() {
        return HealthRegion::all()->pluck('hr_uid')->toArray();
    }

    /**
     * validates Y-m-d
     */
    static function isValidDate($date) {
        $dt = DateTime::createFromFormat("Y-m-d", $date);
        return $dt !== false && !array_sum($dt::getLastErrors());
    }

    /**
     * takes an array of objects(array), then finds and fills any missing dates
     * missing rows will be copy of the earliest known row
     * $data: the array of objects(array); must be sorted earliest-to-newest
     * $reset_row: optional array that is merged (will override attributes)
     * $date_attr: optional key for date
     */
    static function fillMissingDates( $data, $reset_row = [], $date_attr = 'date' ) {

        $filler = [];
        for( $i = 0; $i < count($data) - 1; ++$i ) {
            $date1 = new DateTime( $data[$i][$date_attr] );
            $date2 = new DateTime( $data[$i+1][$date_attr] );
            // check if dates are one day apart or not
            $diff = $date1->diff( $date2 );
            if( $diff->days > 1 ) {
                // base on difference in days, loop out
                for( $j = 1; $j < $diff->days; $j++ ) {
                    // copy and merge reset row, which nulls new_ values
                    $new_row = array_merge( $data[$i], $reset_row );
                    // set date
                    $new_row[$date_attr] = $date1->modify('+1 day')->format('Y-m-d');
                    // stash missing date
                    $filler[] = [
                        'pos' => $i+1,
                        'row' => $new_row,
                    ];
                }
            }
        }
        // now loop through stash of missing dates
        foreach( $filler as $index => $fill ) {
            array_splice( $data, $fill['pos'] + $index, 0, [$fill['row']] );
        }

        return $data;

    }

}