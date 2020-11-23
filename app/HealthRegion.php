<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthRegion extends Model
{
    protected $table = 'health_regions';

    protected $primaryKey = 'hr_uid';

    protected $hidden = array('created_at', 'updated_at');

    public function report()
    {
        return $this->hasMany('App\HrReport', 'hr_uid', 'hr_uid');
    }
}
