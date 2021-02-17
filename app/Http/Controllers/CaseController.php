<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Common;
use App\Province;
use App\HealthRegion;
use App\Cases;
use App\Fatality;

class CaseController extends Controller
{

    /**
     * old function to get summary of all provinces
     * this has been superceded by ReportController@summaryProvince
     */
    public function summary() {
        $result = [
            'total_cases' => Cases::count(),
            'total_fatalities' => Fatality::count(),
        ];
        return $result;
    }

    /**
     * old function to get summary split into provinces
     * this has been superceded by ReportController@summaryProvince
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

    /**
     * get list of cases
     *  - $province: optional province code
     */
    public function list(Request $request, $province = null) {

        // province support
        if( $request->province ) {
            $province = $request->province;
        }

        // hr_uid support
        $hr_uid = null;
        if( $request->hr_uid ) {
            $hr_uid = $request->hr_uid;
        }

        // pagination
        $per_page = 100;
        if( $request->per_page ) {
            $num = (int) $request->per_page;
            $per_page = max( min( $request->per_page, 1000 ), 1);
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
            ->when( $hr_uid, function($query) use ($hr_uid) {
                return $query->where('hr_uid', $hr_uid);
            })
            ->orderBy('id', $order)
            ->paginate($per_page);

        return $cases;
    }

    /**
     * get specific case by id
     */
    public function get($id) {
        return Cases::find($id);
    }

}
