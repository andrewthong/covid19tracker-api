<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Note;

class NoteController extends Controller
{
    /**
     * retrieve all notes
     * tag (optional)
     * 
     */
    public function all( Request $request, $tag = null ) {

        $get_expired = false;
        if( $request->expired ) $get_expired = true;

        return Note::expired( $get_expired )
            ->when($tag, function( $query, $tag ) {
                return $query->where('tag', $tag);
            })
            ->orderByRaw('ISNULL(priority), priority ASC, expiry_date ASC')
            ->get();
    }

    /**
     * retrieve a specific note
     */
    public function get( Request $request, $note_id ) {
        return Note::find( $note_id );
    }

}
