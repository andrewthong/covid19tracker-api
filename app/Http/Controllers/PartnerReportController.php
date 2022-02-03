<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Common;
use App\Option;
use App\Province;
use App\ProcessedReport;

/**
 * generic controller for specific partner endpoints
 * not intended for public use
 */
class PartnerReportController extends Controller
{
    /**
     * partner report to return health region vaccination reports
     * env: PARTNER01
     */
    public function getHealthRegionVaccineReport(Request $request) {

        // if env is not set, return empty
        if(!env('PARTNER01')) {
            return \response()->json(null);
        }

        // using a specifc key for now; until option support is added
        $cache_key = 'partner_01_hr_vaccination_report';

        $value = Cache::rememberForever( $cache_key, function() use ($request) {

            // specific attributes to select
            $select_core = [
                'date',
                'hr_uid',
                'total_vaccinations',
                'total_vaccinated',
                'total_boosters_1',
                'total_boosters_2',
            ];

            $table = 'processed_hr_reports';

            // get earliest date
            $result = DB::table( $table )
                ->select('date')
                ->whereNotNull('total_vaccinations')
                ->orWhereNotNull('total_vaccinated')
                ->first();
            $earliest_date = $result->date;

            // safeguard if no data
            if( !$earliest_date ) {
                return \response()->json(null);
            }

            // prepare SELECT
            $select_stmt = implode(",", $select_core);

            // prepare WHERE
            $where_stmt = "WHERE `date` >= '{$earliest_date}'";

            // DB query
            $data = DB::select("
                SELECT
                    {$select_stmt}
                FROM
                    {$table}
                {$where_stmt}
                ORDER BY
                    `date`, `hr_uid`
            ");

            $last_run = Common::getLastUpdated( 'healthregion' );

            $response = [
                'last_updated' => $last_run,
                'data' => $data,
            ];

            return response()->json($response)->setEncodingOptions(JSON_NUMERIC_CHECK);
            
        });//cache closure

        return $value;

    }
}
