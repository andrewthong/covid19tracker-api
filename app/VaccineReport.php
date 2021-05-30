<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineReport extends Model
{
    protected $table = 'vaccine_reports';

    protected $guarded = [];

    // not using eloquent timestamps
    public $timestamps = false;
}
