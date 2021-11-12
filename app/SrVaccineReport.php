<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SrVaccineReport extends Model
{
    protected $table = 'sr_vaccine_reports';

    // not using eloquent timestamps
    public $timestamps = false;

    protected $guarded = [
        'id',
    ];

    const base_attrs = [
    ];

    const stat_attrs = [
        'total_dose_1',
        'percent_dose_1',
        'source_dose_1',
        'total_dose_2',
        'percent_dose_2',
        'source_dose_2',
        'total_dose_3',
        'percent_dose_3',
        'source_dose_3'
    ];

    public static function statAttrs()
    {
        return static::stat_attrs;
    }

    public static function allAttrs() {
        return array_merge( static::base_attrs, static::stat_attrs );
    }

    public function region() {
        return $this->belongsTo(SubRegion::class, 'code', 'code');
    }
}
