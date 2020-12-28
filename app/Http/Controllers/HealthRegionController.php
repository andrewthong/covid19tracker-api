<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Common;
use App\Option;
use App\HealthRegion;
use App\HrReport;

class HealthRegionController extends Controller
{
    //
    public function reports( $hr_uid ) {
        $reports = HrReport::with('healthRegion:hr_uid,province,engname,frename')
            ->where('hr_uid', $hr_uid)
            ->get();

        return [
            'data' => $reports,
        ];
    }

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
