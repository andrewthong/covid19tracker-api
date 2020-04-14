<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    // not using eloquent timestamps
    public $timestamps = false;

    protected $appends = array('density');

    public function getDensityAttribute() {
        return $this->population / $this->area;
    }
}
