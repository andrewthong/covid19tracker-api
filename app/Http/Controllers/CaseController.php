<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Cases;
use App\Fatality;

class CaseController extends Controller
{

    /*
        summary of cases
    */
    public function summary() {
        $result = [
            'total_cases' => Cases::count(),
            'total_fatalities' => Fatality::count(),
        ];
        return $result;
    }

    /*
        return count of cases by province
    */
    public function summaryProvince() {
        $fatalities = DB::table('fatalities')
            ->selectRAW('fatalities.province, count(fatalities.province) as total_fatalities')
            ->groupBy('fatalities.province');

        $result = DB::table('cases')
            ->selectRaw('cases.province, count(cases.province) as total_cases, ifnull(total_fatalities, 0) as total_fatalities')
            ->leftJoinSub($fatalities, 'fatalities', function ($join) {
                $join->on('cases.province', '=', 'fatalities.province');
            })
            ->groupBy('cases.province')
            ->get();
        return $result;
    }

    /*
        return all province information
    */
    public function provinces() {
        return DB::table('provinces')->get();
    }

    /*
        returns the number of daily cases
        $province:
    */
    public function casesByDate( Request $request, $province = null ) {
        // query to get daily case totals
        $cases = Cases::groupBy( 'date' )
            ->selectRaw( 'DATE_FORMAT(date, \'%Y-%m-%d\') as date, count(id) as total' )
            ->when( $province, function($query) use ($province) {
                // if a province is provided; otherwise all
                return $query->where('province', $province);
            })
            ->orderBy('date')
            ->get();
        
        // grab the first and last date
        $first_date = $cases->first()->date;
        $last_date = $cases->last()->date;
        // generate an array of dates between the two dates
        $dates = Common::getDateArray( $first_date, $last_date );
        // default to no daily case totals
        $base_dates = array_fill_keys( $dates, 0 );

        // loop through results and fill in daily totals
        foreach( $cases as $item ) {
            $base_dates[ $item->date ] = $item->total;
        };

        return [
            'province' => $province ? $province : 'All',
            'data' => $base_dates,
            'first_date' => $first_date,
            'last' => $last_date,
        ];
    }
}
