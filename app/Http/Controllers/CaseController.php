<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Province;
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
        TODO: move to provinces controller
    */
    public function provinces() {
        // return DB::table('provinces')->get();
        $provinces = Province::all();
        return $provinces;
    }

    /*
    cases
     */
    public function list(Request $request, $province = null) {

        // province support
        if( $request->province ) {
            $province = $request->province;
        }

        // pagination
        $per_page = 100;
        if( is_int($request->per_page) ) {
            $per_page = max( $request->per_page, 1000 );//limit
        }

        $order = 'DESC';
        $order_by = 'id';
        if( $request->order === 'ASC' ) {
            $order = $request->order;
        }

        $cases = DB::table('cases')
            ->when( $province, function($query) use ($province) {
                // if a province is provided; otherwise all
                return $query->where('province', $province);
            })
            ->orderBy('id', $order)
            ->paginate($per_page);

        return $cases;
    }

    /*
    get specific case
     */
    public function get($id) {
        return Cases::find($id);
    }

    /*
     utility function to convert provinces attribute in cases
     $to_code: if true, converts names to code where applicable
     */
    public function transformProvinces( $to_code = true ) {
        // modular from-to
        $vfrom = 'name';
        $vto = 'code';
        // swap them around (from code to names)
        if( $to_code !== false ) {
            list( $vto, $vfrom ) = array( $vto, $vfrom );
        }
        // get all provinces
        $provinces = Province::all()->toArray();
        $result = array();
    
        foreach($provinces as $province) {
            // run an update statement for each province on cases
            // Eloquent equivalent to
            //   update `cases` set `province` = 'code_or_name' where `province` = 'name_or_code' 
            $affected_rows = Fatality::where( 'province', $province[$vfrom] )
                ->update( ['province' => $province[$vto]] );
            $result[] = array(
                'from' => $province[$vfrom],
                'to' => $province[$vto],
                'affected_rows' => $affected_rows,
            );
        }
        return $result;
    }

    /*
     this utility function takes counts cases and fatalities to fill the reports
     needed for reporting unless reports also include cases and fatalities
     current tracking logs cases and fatalities on an individual level
    */
    public function fillReports( $fill_with = ['cases', 'fatalities'] ) {

        $provinces = Province::all()->toArray();
        
        // query to count daily cases and fatalities from individual db
        $records = DB::select("
            SELECT
                province,
                day,
                COUNT(c_id) as cases,
                COUNT(f_id) as fatalities
            FROM (
                SELECT
                    province,
                    DATE(`date`) AS day,
                    id AS c_id,
                    null AS f_id
                FROM 
                    `cases`
                UNION
                SELECT
                    province,
                    DATE(`date`) AS day,
                    null as c_id,
                    id AS f_id
                FROM
                    `fatalities`
            ) AS un
            GROUP BY
                day,
                province
            ORDER BY
                day
        ");

        $response = [];

        foreach( $records as $record ) {
            DB::table('reports')
                ->updateOrInsert(
                    [
                        'date' => $record->day,
                        'province' => $record->province
                    ],
                    [
                        'date' => $record->day,
                        'province' => $record->province,
                        'cases' => $record->cases,
                        'fatalities' => $record->fatalities,
                    ]
                );
        }

        return $response;
        
    }

}
