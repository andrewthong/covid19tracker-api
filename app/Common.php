<?php

namespace App;

use DateTime;

use App\HealthRegion;
use App\Province;
use App\Option;

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
            'vaccinated',
        ];
        // province specific fields
        if( $p === true || $p === 'province' ) {
            $core_attrs = array_merge($core_attrs, [
                'vaccines_distributed',
            ]);
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
     * prepends an array of prefixes to each item of a given array
     * $arr (array)
     * - list of strings
     * optional $prefixes (array)
     * - list of prefixes to be prepended
     */
    static function prefixArrayItems($arr, $prefixes = null) {
        // defaults to change and total
        if(!$prefixes) {
            $prefixes = ['change_', 'total_'];
        }
        $new_arr = [];
        foreach( $prefixes as $prefix ) {
            foreach( $arr as $attr ) {
                $new_arr[] = "{$prefix}{$attr}";
            }
        }
        return $new_arr;
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
     *   province: code to retrieve hr_uid of particular province
     */
    static function getHealthRegionCodes($province = null) {
        $health_regions = null;
        if( $province ) {
            $health_regions = HealthRegion::where('province', $province);
        } else {
            $health_regions = HealthRegion::all();
        }
        return $health_regions->pluck('hr_uid')->toArray();
    }

    /**
     * validates Y-m-d
     */
    static function isValidDate($date) {
        $dt = DateTime::createFromFormat("Y-m-d", $date);
        return $dt !== false && !array_sum($dt::getLastErrors());
    }

    /**
     * helper to get updated_at
     *  $location: code for province (or 'healthregion')
     * falls back to last_processed for province/healthregion
     */
    static function getLastUpdated( $location = null ) {
        $option_last = 'report_last_processed';
        // health region specific
        if( $location === 'healthregion' ) {
            $option_last = 'report_hr_last_processed';
        }
        if( $location === 'healthregion' || $location === 'province' ) {
            // null to fallback to option
            $location = null;
        }
        if( $location ) {
            $province = Province::where('code', $location)->first();
            if( $province ) {
                return $province->updated_at->format('Y-m-d H:i:s');
            }
        }
        return Option::get($option_last);
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

    /**
     * helper for v2 report system
     * returns array of tables using this system
     */
    public static function availableReports() {
        return [
            'vaccine_reports',
        ];
    }

    /**
     * helper for v2 report system
     * takes attrs and optionally splits them into change and total groups
     * e.g. ['change_attrs'=>[...], 'total_attrs'=>[...]]
     */
    public static function attrsHelper( $attrs = [], $split = false ) {
        $response = $attrs; // $split === false (default)
        if( $split ) {
            $split_groups = ['change', 'total'];
            $response = [];
            foreach($split_groups as $s ) {
                $group_key = "{$s}_attrs";
                $response[$group_key] = [];
                foreach( $attrs as $key => $attr ) {
                    // check if attr starts with
                    if( strpos($attr, "{$s}_") === 0 ) {
                        $response[$group_key][] = substr( $attr, strlen($s)+1 );
                        unset($attrs[$key]); // remove it from base array
                    }
                }
            }
        }
        return $response;
    }

    /**
     * helper for v2 report system
     * checks if province is whitelisted for a given report table
     * by default if no whitelist is found, assumes all provinces allowed
     */
    public static function isProvinceEnabledForReport( $province, $report_table ) {
        // get province whitelist
        $enabled_provinces = Option::get("{$report_table}_enabled_provinces");
        // convert to array
        $enabled_provinces = $enabled_provinces ? explode( ',', $enabled_provinces ) : false;
        // if not set, allow all otherwise check if province is whitelisted before proceeding
        return !$enabled_provinces || in_array( $province, $enabled_provinces );
    }

    /**
     * helper to check if all keys in an object are null
     */
    public static function containsOnlyNull( $obj ) {
        return empty(array_filter($obj, function ($a) { return $a !== null;}));
    }

}