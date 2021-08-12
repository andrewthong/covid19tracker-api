<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Common;

/**
 * v2 report system: modular reporting tables
 * see README-report.md
 */
class ModularReport extends Model
{

    protected $guarded = [];

    // not using eloquent timestamps
    public $timestamps = false;

    // reporting attributes
    // processed and reference attributes must begin with change_ or total_

    // processed attributes are derived from reference data
    const processed_attrs = [];

    // reference attributes are provided by data-entry activities
    const reference_attrs = [];

    // other attributes that are not processed
    // Note: id, date, province does not need to be included here
    const other_attrs = [];

    /**
     * attributes that the processing utility use (reference)
     *  $split: when true, splits attrs into total and change keys
     */
    public static function referenceAttrs( $split = false ) {
        return Common::attrsHelper( static::reference_attrs, $split );
    }

    /**
     * attributes that are generated from processing
     */
    public static function processedAttrs( $split = false ) {
        return Common::attrsHelper( static::processed_attrs, $split );
    }

    /**
     * return all attributes
     */
    public static function allAttrs() {
        return array_merge( static::processed_attrs, static::reference_attrs, static::other_attrs );
    }

    /**
     * helper to retrieve table name
     */
    public static function getTableName() {
        return (new static())->getTable();
    }

    /**
     * helper to pull latest records for all unique provinces
     */
    public static function latest() {
        $table = static::getTableName();

        $select_core = array_merge(
            ['t1.province', 'date'],
            static::allAttrs()
        );

        $select_stmt = implode( ",", $select_core );

        $query = "SELECT {$select_stmt} FROM {$table} t1 JOIN (SELECT province, MAX(`date`) as latest_date FROM {$table} group by `province`) t2 ON t1.province = t2.province AND t1.date = t2.latest_date";

        $report = DB::select($query);

        $response = [
            'data' =>  $report,
        ];

        return $response;
    }

    /**
     * helper to pull the latest record for the specified province
     */
    public static function latestByProvince( $province ) {
        $report = static::select(
                array_merge(['province', 'date'], static::allAttrs())
            )->where('province', $province)
            ->latest('date')
            ->first();

        $response = [
            'data' =>  $report,
        ];

        return $response;
    }

}
