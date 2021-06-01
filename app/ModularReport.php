<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return Common::attrsHelper( self::reference_attrs, $split );
    }

    /**
     * attributes that are generated from processing
     */
    public static function processedAttrs( $split = false ) {
        return Common::attrsHelper( self::processed_attrs, $split );
    }

    /**
     * return all attributes
     */
    public static function allAttrs() {
        return array_merge( self::processed_attrs, self::reference_attrs, self::other_attrs );
    }

}
