<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Fatality;

class FatalityController extends Controller
{
    /*
    fatalities list
     */
    public function list(Request $request, $province = null) {

        // province support
        if( $request->province ) {
            $province = $request->province;
        }

        // pagination
        $per_page = 100;
        if( $request->per_page ) {
            $num = (int) $request->per_page;
            $per_page = max( min( $request->per_page, 1000 ), 1);//limit
        }

        $order = 'DESC';
        $order_by = 'id';
        if( $request->order === 'ASC' ) {
            $order = $request->order;
        }

        $cases = DB::table('fatalities')
            ->when( $province, function($query) use ($province) {
                // if a province is provided; otherwise all
                return $query->where('province', $province);
            })
            ->orderBy('id', $order)
            ->paginate($per_page);

        return $cases;
    }

    /*
    get specific fatality
     */
    public function get($id) {
        return Fatality::find($id);
    }
}
