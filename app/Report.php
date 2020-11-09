<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'criticals',
        'hospitalizations',
        'tests',
        'recoveries',
    ];

    // not using eloquent timestamps
    public $timestamps = false;

    // empty guarded means all mass assignable
    protected $guarded = [
        'id',
    ];
}
