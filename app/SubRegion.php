<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubRegion extends Model
{
    protected $table = 'sub_regions';

    protected $primaryKey = 'code';
    public $incrementing = false;

    protected $hidden = array('created_at', 'updated_at');

    public function vaccineReport()
    {
        return $this->hasMany('App\SrVaccineReport', 'code', 'code');
    }

    public function parent()
    {
        return $this->belongsTo('App\Province', 'province', 'code');
    }

    public static function getCodes($province = null)
    {
        $sub_regions = null;
        if( $province ) {
            $sub_regions = self::where('province', $province);
        } else {
            $sub_regions = self::all();
        }
        return $sub_regions->pluck('code')->toArray();
    }

}
