<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    public $timestamps = ['updated_at'];

    protected $appends = array('density');

    public function getDensityAttribute() {
        if( $this->area > 0 )
            return $this->population / $this->area;
        return null;
    }
}
