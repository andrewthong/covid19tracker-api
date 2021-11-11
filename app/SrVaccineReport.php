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

    public function region()
    {
        return $this->belongsTo(SubRegion::class, 'code', 'code');
    }
}
