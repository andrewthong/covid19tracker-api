<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Province;
use App\HealthRegion;

class ProvinceController extends Controller
{
    /*
        return all province information
    */
    public function list( Request $request ) {
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request) {
            $provinces = Province::query();
            if( request('geo_only') )
                $provinces->where( 'geographic', 1 );
            return $provinces->get();
        });//cache closure
        return $value;
    }

    /*
        return health regions in a given province
    */
    public function healthRegions( Request $request, $province = null ) {
        $cache_key = $request->getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($request, $province) {
            return HealthRegion::where( 'province', $province )->get();
        });
        return $value;
    }
}
