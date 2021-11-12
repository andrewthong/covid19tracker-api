<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\SubRegion;

class SubRegionController extends Controller
{
    public function regions( $code = null ) {
        $regions = [];
        if( $code ) {
            $regions = SubRegion::find( $code );
        } else {
            $regions = SubRegion::all();
        }
        return [
            'data' => $regions,
        ];
    }
}
