<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Common;
use App\Utility;
use App\Province;
use App\HealthRegion;
use App\HrReport;
use App\Report;
use App\ProcessQueue;

use App\VaccineReport;

class ManageController extends Controller
{

    public function getReports( Request $request, $province ) {
        $provinces = Common::getProvinceCodes( false );
        $date = $request->date;
        // ensure valid date
        if( !Common::isValidDate( $date ) ) {
            abort(400, "Invalid date provided");
        }
        // ensure valid province
        if( in_array( $province, $provinces ) ) {
            $response = [];
            $response['province'] = Province::where('code', $province)->first();
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

            // v2 report system
            $response['report_v2'] = [];
            $additional_report_tables = Common::availableReports();
            foreach( $additional_report_tables as $report_table ) {
                if( Common::isProvinceEnabledForReport( $province, $report_table ) ) {
                    $response['report_v2'][$report_table] = [
                        'enabled' => true,
                        'data' => [],
                    ];
                    // helper candidate if list grows
                    if( $report_table === 'vaccine_reports' ) {
                        $base_attrs = array_fill_keys(VaccineReport::referenceAttrs(), null);
                        $base_data = VaccineReport::firstOrNew([
                            'province' => $province,
                            'date' => $date
                        ], $base_attrs )->toArray();
                        // clean up attrs
                        $response['report_v2'][$report_table]['data'] = array_intersect_key( $base_data, $base_attrs );
                    }
                }
            }

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
        if( !Common::isValidProvinceCode( $province_code, false ) ) {
            abort(400, "Invalid report province");
        }

        // validate permission
        $user = auth()->guard('api')->user();
        $user->load(['roles', 'provinces']);
        // not admin
        if( $user->roles->pluck('name')[0] !== 'admin') {
            // doesn't have province assigned
            if( !in_array($province_code, $user->provinces->pluck('code')->toArray()) ) {
                abort(400, "You do not have permission for {$province_code}");
            }
        }

        // core attributes for report and hr report model
        $attrs = array_flip( Common::attributes(null, true) );
        $hr_attrs = array_flip( Common::attributes(null, false) );

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
                $hr_report_values = array_intersect_key( $data, $hr_attrs );
                // update or create HR
                HrReport::updateOrCreate(
                    $where_values,
                    array_merge( $where_values, $hr_report_values )
                );
            }
        }

        // process vaccine report
        if( request('report_v2') ) {
            $report_v2_data = request('report_v2');
            $vaccine_report_data = $report_v2_data['vaccine_reports'];
            if( $vaccine_report_data ) {
                $vaccine_report_attrs = VaccineReport::referenceAttrs(); 
                $where_values = [
                    'province' => $province_code,
                    'date' => $date
                ];
                // only keep core attributes, discard everything else
                $report_values = array_intersect_key( $vaccine_report_data, array_fill_keys($vaccine_report_attrs, null) );
                // update or create
                VaccineReport::updateOrCreate(
                    $where_values,
                    array_merge( $where_values, $report_values )
                );
            }
        }

        // save province status
        $new_status = request('status');
        if(!$new_status) $new_status = "";
        $province = Province::firstWhere('code', $province_code);
        // compare new status
        if( $province->data_status === $new_status ) {
            // update updated_at
            $province->touch();
        } else {
            $province->data_status = $new_status;
            $province->save();
        }

        // store in queue
        $queue_status = ProcessQueue::lineUp($province_code, $date);

        // response
        return response([
            'message' => 'Report saved',
            'province' => $province_code,
            'date' => $date,
            'queue_status' => $queue_status,
        ], 200);

    }

    public function clearCache() {
        return Utility::clearCache();
    }

    public function queueStatus() {
        $waiting = ProcessQueue::orderBy('created_at', 'desc')
            ->where('processed', false)
            ->take(30)
            ->get();
        $processed = ProcessQueue::orderBy('created_at', 'desc')
            ->where('processed', true)
            ->take(30)
            ->get();
        return [
            'waiting' => $waiting,
            'processed' => $processed,
        ];
    }

    public function processQueue() {
        return ProcessQueue::process();
    }

}