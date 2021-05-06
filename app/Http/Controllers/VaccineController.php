<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\VaccineDistribution;
use App\Common;

class VaccineController extends Controller
{

    /**
     * get latest vaccine distribution records
     */
    public function distribution( $split = false ) {

        // cache
        $cache_key = \Request::getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use( $split ) {

            $table = 'vaccine_distribution';

            // meta
            $last_run = Common::getLastUpdated();

            // attrs
            $core_attrs = [
                'pfizer_biontech',
                'moderna',
                'astrazeneca',
                'johnson',
            ];

            // provinces
            $location_codes = Common::getProvinceCodes();
            $location_col = 'province';
            
            // preparing SQL query
            $select_core = [];
            $date_select = "MAX(date) AS latest_date";
            $stat_select = 'SUM(%1$s) AS %1$s';

            // $split modifiers, we no longer need to group
            if( $split ) {
                $select_core[] = $location_col;
                $date_select = "date";
                $stat_select = '%1$s';
            }

            // prepare select
            $select_core[] = $date_select;
            foreach( $core_attrs as $attr ) {
                $select_core[] = sprintf( $stat_select, "{$attr}" );
                // administered count
                $select_core[] = sprintf( $stat_select, "{$attr}_administered" );
            }

            // 
            $subquery_core = [];
            $subquery_stmt = '';
            $query = '';

            $select_stmt = implode( ",", $select_core );
            foreach( $location_codes as $lc ) {
                $subquery_core[] = "(
                    SELECT *
                    FROM {$table}
                    WHERE
                        {$location_col}='{$lc}'
                    ORDER BY `date` DESC
                    LIMIT 1
                )";
            }
            $subquery_stmt = implode( " UNION ", $subquery_core );
            $query = "
                SELECT
                    {$select_stmt}
                FROM (
                    {$subquery_stmt}
                ) pr
            ";

            $report = DB::select($query);

            $response = [
                'data' =>  $report,
                'last_updated' => $last_run,
            ];

            // return to be stored in
            return $response;

        });//cache closure

        return $value;

    }

}
