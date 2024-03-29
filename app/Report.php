<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'province',
        'date',
        'cases',
        'fatalities',
        'criticals',
        'hospitalizations',
        'tests',
        'recoveries',
        'vaccinations',
        'vaccines_distributed',
        'vaccinated',
        'boosters_1',
        'boosters_2',
    ];

    // not using eloquent timestamps
    public $timestamps = false;

    // empty guarded means all mass assignable
    protected $guarded = [
        'id',
    ];
}
