<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineAgeGroup extends Model
{
    protected $table = 'vaccine_group';

    protected $guarded = [];

    public $timestamps = false;
}
