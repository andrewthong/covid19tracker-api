<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Common;
use App\Province;
use App\HealthRegion;
use App\HrReport;
use App\Report;

class ManageController extends Controller
{

    public function getReports( Request $request, $province ) {
        $provinces = Common::getProvinceCodes();
        $date = $request->date;
        // ensure valid date
        if( !Common::isValidDate( $date ) ) {
            abort(400, "Invalid date provided");
        }
        // ensure valid province
        if( in_array( $province, $provinces ) ) {
            $response = [];
            $response['report'] = Report::firstOrNew([
                'province' => $province,
                'date' => $date
            ]);
            $regions = HealthRegion::where(['province' => $province]);
            $hr_uids = $regions->pluck('hr_uid')->toArray();
            $response['regions'] = $regions->get();
            $response['hr_reports'] = HrReport::whereIn('hr_uid', $hr_uids)->where([
                'date' => $date
            ])->get();
            return $response;
        }
        return response([
            'message' => "Invalid province ({$province}) selected",
        ], 400);
    }

    public function saveReports( Request $request ) {

        // validate date
        $date = request('date');
        if( !Common::isValidDate( $date ) ) {
            abort(400, "Invalid report date");
        }

        // validate province
        $province_code = request('province');
        if( !Common::isValidProvinceCode( $province_code, true ) ) {
            abort(400, "Invalid report province");
        }

        // core attributes for report model
        $attrs = array_flip( Common::attributes() );

        // process (province) report
        if( request('report') ) {
            $where_values = [
                'province' => $province_code,
                'date' => $date
            ];
            // only keep core attributes, discard everything else
            $report_values = array_intersect_key( request('report'), $attrs );
            // update or create
            Report::updateOrCreate(
                $where_values,
                array_merge( $where_values, $report_values )
            );
        }

        // process hr report entries
        if( request('hr_report') ) {
            foreach (request('hr_report') as $hr_uid => $data) {
                $where_values = [
                    'hr_uid' => $hr_uid,
                    'date' => $date
                ];
                // same as province-wide report
                $hr_report_values = array_intersect_key( $data, $attrs );
                // update or create HR
                HrReport::updateOrCreate(
                    $where_values,
                    array_merge( $where_values, $hr_report_values )
                );
            }
        }

        // save province status
        $new_status = request('status');
        $province = Province::firstWhere('code', $province_code);
        $province->data_status = $new_status;
        $province->save();

        // response
        return response([
            'message' => 'Report saved',
            'province' => $province_code,
            'date' => $date,
        ], 200);

    }

}