<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// have to use plural as case is a reserved word
class Cases extends Model
{
    protected $table = 'cases';

    // not using eloquent timestamps
    public $timestamps = false;

    // empty guarded means all mass assignable
    protected $guarded = [
        'id',
    ];
}