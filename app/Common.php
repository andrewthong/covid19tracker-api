<?php

namespace App;

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

}