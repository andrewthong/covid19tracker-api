<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fatality extends Model
{
    protected $table = 'fatalities';

    // not using eloquent timestamps
    public $timestamps = false;
}
