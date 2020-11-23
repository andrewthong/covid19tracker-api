<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HrReport extends Model
{
    protected $table = 'hr_reports';

    // not using eloquent timestamps
    public $timestamps = false;

    protected $guarded = [
        'id',
    ];

    public function healthRegion()
    {
        return $this->belongsTo('App\HealthRegion', 'hr_uid', 'hr_uid');
    }
}
