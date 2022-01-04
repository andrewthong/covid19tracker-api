<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\RapidTest;

class RapidTestController extends Controller
{
    
    public function submit(Request $request)
    {
        
        // simple validation; not using validator
        $errors = [];

        $fields = [
            'age',
            'postal_code',
            'test_date',
            'test_result',
        ];
        
        foreach( $fields as $field ) {
            if( !$request->has( $field ) ) {
                $errors []= 'Missing '.$field;
            }
        }

        // validate age (5 characters)
        if( $request->has('age') ) {
            $age = substr(trim($request->age), 0, 5);
        }

        // 3-char postal code
        if( $request->has('postal_code') ) {
            $postal_code = strtoupper(substr(trim($request->postal_code), 0, 3));
        }

        // validate date
        if( $request->has('test_date') ) {
            $test_date = substr(trim($request->test_date), 0, 10);
            if( !preg_match('/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $test_date) ) {
                $errors []= 'Invalid date';
            }
        }

        // validate result
        if( $request->has('test_result') ) {
            $test_result = strtolower(trim($request->test_result));
            if( !in_array($test_result, ['positive', 'negative', 'invalid result']) ) {
                $errors []= 'Invalid test result';
            }
        }

        // check for errors
        if( empty( $errors ) ) {
            $record = new RapidTest;
            $record->age = $age;
            $record->postal_code = $postal_code;
            $record->test_date = $test_date;
            $record->test_result = $test_result;
            // save
            $record->save();
            return response()->json(['created' => true]);
        } else {
            // return errors
            return response()->json(['created' => false, 'errors' => $errors]);
        }
    }

}
