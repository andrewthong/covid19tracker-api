<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';
    
    protected $fillable = [
        'key',
        'value',
    ];

    static function get( $attribute ) {
        if( $option = self::where('attribute', $attribute)->first() ) {
            return $option->value;
        }
        return '';
    }

    static function set( $attribute, $value ) {
        return self::updateOrInsert(
            ['attribute' => $attribute],
            ['value' => $value]
        );
    }

}
