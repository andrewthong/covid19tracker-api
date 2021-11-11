<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubRegion extends Model
{
    protected $table = 'sub_regions';

    public $autoincrement = false;

    protected $primaryKey = 'code';

    protected $hidden = array('created_at', 'updated_at');

    public function vaccineReport()
    {
        return $this->hasMany('App\SrVaccineReport', 'code', 'code');
    }
}
