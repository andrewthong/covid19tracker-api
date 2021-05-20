<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\VaccineDistribution;
use App\VaccineAgeGroup;
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

    /**
     * [helper] get vaccine age group reports by province
     */
    public function ageGroupByProvince( Request $request, $province ) {
        return $this->ageGroup( $request, false, $province );
    }

    /**
     * get vaccine age group records
     * $ssplit (bool): use designated _ALL for whole country if false otherwise returns all provinces
     * $province (str): if set, will return only records for the province
     */
    public function ageGroup( Request $request, $split = false, $province = null ) {

        // cache
        $cache_key = \Request::getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use( $request, $split, $province ) {

            $table = 'vaccine_groups';

            $select_core = [
                'date'
            ];

            // select a specific age group
            // MySQL 8.0 has JSON_TABLE which could allow for selecting specific stats
            if( $request->group ) {
                $select_core[] = "JSON_EXTRACT(`data`, '$.\"{$request->group}\"') AS data";
            } else {
                // default
                $select_core[] = 'data';
            }

            $where_core = [];

            // check for province
            if( $province ) {
                $where_core[] = "province = '{$province}'";
            // _ALL is aggregate data aka "Canada"
            } else if( $split ) {
                $select_core[] = 'province';
                $where_core[] = "province != '_ALL'";
            } else {
                $where_core[] = "province = '_ALL'";
            }

            // before and after date
            if( $request->after ) {
                $where_core[] = "`date` >= '{$request->after}'";
            }
            if( $request->before ) {
                $where_core[] = "`date` <= '{$request->before}'";
            }

            // query
            $select_stmt = implode( ",", $select_core );
            $where_stmt = "";
            if( $where_core ) {
                $where_stmt = "WHERE " . implode(" AND ", $where_core);
            }

            $query = "SELECT {$select_stmt} FROM {$table} {$where_stmt} ORDER BY `date` ASC";

            $report = DB::select($query);

            $response = [
                'province' => $province ? $province : 'All',
                'data' =>  $report,
            ];

            return $response;

        });//cache closure

        return $value;

    }

}
