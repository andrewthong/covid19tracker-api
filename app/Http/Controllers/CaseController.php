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
        produces report with daily and cumulative totals for key attributes
    */
    public function report( Request $request, $province = null ) {
        $data = DB::select('
            SELECT
                r.date,
                tests_ct as daily_tests,
                @tests_cu:=@tests_cu + r.tests_ct as cumu_tests,
                cases_ct as daily_cases,
                @cases_cu:=@cases_cu + r.cases_ct as cumu_cases,
                hosptl_ct as daily_hospitalizations,
                @hosptl_cu:=@hosptl_cu + r.hosptl_ct as cumu_hospitalizations,
                criticals_ct as daily_criticals,
                @criticals_cu:=@criticals_cu + r.criticals_ct as cumu_criticals,
                recoveries_ct as daily_recoveries,
                @recoveries_cu:=@recoveries_cu + r.recoveries_ct as cumu_recoveries,
                fatalities_ct as daily_fatalities,
                @fatalities_cu:=@fatalities_cu + r.fatalities_ct as cumu_fatalities
            FROM (
                SELECT
                    `date`,
                    SUM(tests) AS tests_ct,
                    SUM(cases) AS cases_ct,
                    SUM(hospitalizations) AS hosptl_ct,
                    SUM(criticals) AS criticals_ct,
                    SUM(recoveries) AS recoveries_ct,
                    SUM(fatalities) AS fatalities_ct
                FROM reports
                # WHERE province = "bb"
                GROUP BY `date`
            ) AS r
            JOIN (SELECT
                @cases_cu:=0,
                @tests_cu:=0,
                @hosptl_cu:=0,
                @criticals_cu:=0,
                @recoveries_cu:=0,
                @fatalities_cu:=0
            ) j
            ORDER BY r.date
        ');

        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
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
