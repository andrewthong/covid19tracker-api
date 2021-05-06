<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineDistribution extends Model
{
    protected $table = 'vaccine_distributed';

    protected $guarded = [];

    // not using eloquent timestamps
    public $timestamps = false;
}