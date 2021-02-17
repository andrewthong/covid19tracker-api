<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Province;
use App\HealthRegion;

class ProvinceController extends Controller
{
    /**
     * get all provinces
     */
    public function list( Request $request ) {
        // cache
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request) {
            $provinces = Province::query();
            if( request('geo_only') )
                $provinces->where( 'geographic', 1 );
            return $provinces->get();
        });//cache closure
        return $value;
    }

    /**
     * get a specific province
     *  - $province: code e.g. SK
     */
    public function get( Request $request, $province = null ) {
        // cache
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $province) {
            return Province::where( 'code', $province )->get();
        });
        return $value;
    }

    /**
     * return health regions in a given province
     *  - $province: code e.g. SK
     */
    public function healthRegions( Request $request, $province = null ) {
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $province) {
            return HealthRegion::where( 'province', $province )->get();
        });
        return $value;
    }
}
