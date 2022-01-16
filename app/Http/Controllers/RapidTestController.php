<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon;

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
        
        // recaptcha
        $recaptcha_secret = env('RECAPTCHA_SECRET_KEY');
        if( $recaptcha_secret ) {
            if( !$request->has('g-recaptcha-response') ) {
                $errors []= 'Missing captcha';
            } else {
                $captcha = $request['g-recaptcha-response'];
                // post request to server
                $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptcha_secret) .  '&response=' . urlencode($captcha);
                $recaptcha_response = file_get_contents($recaptcha_url);
                $recaptcha_response_keys = json_decode($recaptcha_response,true);
                if( !$recaptcha_response_keys["success"] ) {
                    $errors []= 'Captcha failed. Please try again.';
                }
            }
        }

        // validate age (5 characters)
        if( $request->has('age') ) {
            $age = substr(trim($request->age), 0, 5);
        }

        // 3-char postal code
        if( $request->has('postal_code') ) {
            $postal_code = strtoupper(substr(trim($request->postal_code), 0, 3));
            // validate postal code
            if( !preg_match('/^[ABCEGHJKLMNPRSTVXY]{1}[0-9]{1}/', $postal_code) ) {
                $errors []= 'Invalid postal code';
            }
        }

        // validate date
        if( $request->has('test_date') ) {
            $test_date = substr(trim($request->test_date), 0, 10);
            if( !preg_match('/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $test_date) ) {
                $errors []= 'Invalid test date';
            }
            // date must be between today and RAPID_TESTS_START_DATE
            $min_date = env('RAPID_TESTS_START_DATE', '2021-12-01');
            $min_u = strtotime($min_date);
            $max_u = Carbon\Carbon::now('America/St_Johns')->timestamp;
            $max_date = date('Y-m-d', $max_u);
            $the_date = strtotime($test_date);
            if( $the_date < $min_u ) {
                $errors []= "Test date cannot be before {$min_date}";
            }
            if( $the_date > $max_u ) {
                $errors []= "Test date cannot be after {$max_date}";
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
            $record->ip = $request->ip();
            // save
            $record->save();
            return response()->json(['created' => true]);
        } else {
            // return errors
            return response()->json(['created' => false, 'errors' => $errors]);
        }
    }

    /**
     * return summary of rapid tests
     */
    public function summary() {
        // cache
        $cache_key = \Request::getRequestUri();
        $value = Cache::rememberForever( $cache_key, function() use ($split, $type) {

            $response = [
                'test_results' => [],
                'test_dates' => [],
            ];

            $response['total'] = RapidTest::count();

            $results = RapidTest::getTestResultsTypes();
            foreach( $results as $result ) {
                $response['test_results'][$result] = RapidTest::where('test_result', $result)->count();
            }
            $response['test_dates']['earliest'] = RapidTest::orderBy('test_date', 'asc')->first()->test_date->format('Y-m-d');
            $response['test_dates']['latest'] = RapidTest::orderBy('test_date', 'desc')->first()->test_date->format('Y-m-d');

            return response()->json($response);

        });//cache closure

    }

}
