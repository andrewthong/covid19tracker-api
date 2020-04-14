<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('summary', 'CaseController@summary');
Route::get('summary/province', 'CaseController@SummaryProvince');

Route::get('report', 'CaseController@Report');

Route::get('provinces', 'CaseController@Provinces');

Route::get('cases/transform-provinces', 'CaseController@transformProvinces');

Route::get('cases/by-date', 'CaseController@casesByDate');
Route::get('cases/by-date/{province}', 'CaseController@casesByDate');