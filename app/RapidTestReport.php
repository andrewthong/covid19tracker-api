<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RapidTestReport extends Model
{
    
    protected $table = 'rt_reports';

    protected $fillable = [
        'province',
        'date',
        'positive',
        'negative',
        'invalid',
    ];

    // not using eloquent timestamps
    public $timestamps = false;
    
}
