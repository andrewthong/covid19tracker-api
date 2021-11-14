<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\SubRegion;

class SubRegionController extends Controller
{
    
    /**
     * returns all regions unless $code is specified, which then returns only that region (if available)
     */
    public function regions( Request $request, $code = null ) {
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $code) {
            $regions = [];
            if( $code ) {
                $regions = SubRegion::find( $code );
            } else {
                $regions = SubRegion::all();
            }
            return [
                'data' => $regions,
            ];
        });//cache closure
        return $value;
    }

    /*
        return list of all provinces that have sub-regions
    */
    public function provinces( Request $request ) {
        // $cache_key = $request->getRequestUri();
        // $value = Cache::rememberForever( $cache_key, function() use ($request) {
            $provinces = SubRegion::with('parent')->get()->pluck('parent')->unique()->flatten();
            return $provinces;
        // });//cache closure
        // return $value;
    }
}
