<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostalDistrict extends Model
{
    public $timestamps = false;

    static function dictionary() {
        return self::all()
            ->pluck('province', 'letter');
    }
}
