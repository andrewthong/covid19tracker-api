<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cases;
use App\Fatality;

class CaseController extends Controller
{
    /*
        return totals
    */
    public function byProvince() {
        $result = Cases::groupBy('province')
            ->selectRaw('province, count(province) as cases')
            ->get();
        return $result;
    }

    public function summary() {
        $result = [
            'total' => Cases::count(),
            'fatalities' => Fatality::count(),
        ];
        return $result;
    }
}
