<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';
    
    protected $fillable = [
        'title',
        'description',
        'tag',
        'expiry_date',
        'priority',
        'type',
    ];

    public function scopeExpired( $query, $get_expired ) {
        $operand = $get_expired ? '<' : '>=';
        return $query->where( 'expiry_date', $operand, date('Y-m-d') )
            ->orWhereNull( 'expiry_date' );
    }
}
