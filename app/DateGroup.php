<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Cases;
use App\Fatality;

class DateGroup extends Model
{
    protected $table = 'date_group';

    protected $guarded = [
        'id',
    ];

    /*
        populates table based on reports
    */
    static function generate() {
        // start date

        // end date
        
        // pull cases
    }

}
