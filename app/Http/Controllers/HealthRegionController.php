<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Common;
use App\Option;
use App\HealthRegion;
use App\HrReport;

class HealthRegionController extends Controller
{
    /**
     * retrieve all health regions or a specific one
     *  - $hr_uid: optional hr_uid to return single health region
     */
    public function regions( $hr_uid = null ) {
        $regions = [];
        if( $hr_uid ) {
            $regions = HealthRegion::find( $hr_uid );
        } else {
            $regions = HealthRegion::all();
        }
        return [
            'data' => $regions,
        ];
    }
}
