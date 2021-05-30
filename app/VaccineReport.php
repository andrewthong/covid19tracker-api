<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Common;

class VaccineReport extends Model
{
    protected $table = 'vaccine_reports';

    protected $guarded = [];

    // not using eloquent timestamps
    public $timestamps = false;

    /**
     * attrs that manage utility will fill
     *  $split: when true, splits attrs into total and change keys
     */
    public static function attrs( $split = false ) {
        $attrs = [
            'total_adults_vaccinations',
            'total_adults_vaccinated',
        ];
        return Common::attrsHelper( $attrs, $split );
    }

}
