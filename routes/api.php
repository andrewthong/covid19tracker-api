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

Route::get('provinces', 'CaseController@Provinces');

Route::get('report', 'ReportController@generate');
Route::get('report/full', 'Reportcontroller@generateFull');
Route::get('report/province/{province}', 'ReportController@generate')->where('province', '[A-Za-z]+');
Route::get('report/province/{province}/full', 'ReportController@generateFull')->where('province', '[A-Za-z]+');

Route::get('cases', 'CaseController@list');
Route::get('case/{id}', 'CaseController@get')->where('id', '[\d]+');

Route::get('fatalities', 'FatalityController@list');
Route::get('fatality/{id}', 'FatalityController@get')->where('id', '[\d]+');
