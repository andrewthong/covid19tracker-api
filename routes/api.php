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

Route::get('summary', 'ReportController@summary');
Route::get('summary/{split}', 'ReportController@Summary')->where('split', 'split');

Route::get('provinces', 'CaseController@Provinces');

Route::get('reports', 'ReportController@generateProvince');
Route::get('reports/province/{province}', 'ReportController@generateProvince')->where('province', '[A-Za-z_]+');

Route::get('cases', 'CaseController@list');
Route::get('case/{id}', 'CaseController@get')->where('id', '[\d]+');

Route::get('fatalities', 'FatalityController@list');
Route::get('fatality/{id}', 'FatalityController@get')->where('id', '[\d]+');

Route::get('regions', 'HealthRegionController@regions');
Route::get('regions/{hr_uid}', 'HealthRegionController@regions')->where('hr_uid', '[\d]+');
Route::get('regions/{hr_uid}/reports', 'ReportController@generateHealthRegion')->where('hr_uid', '[\d]+');
Route::get('reports/regions', 'ReportController@generateHealthRegion');
Route::get('reports/regions/{hr_uid}', 'ReportController@generateHealthRegion')->where('hr_uid', '[\d]+');

Route::get('notes', 'NoteController@all');
Route::get('notes/tag/{tag}', 'NoteController@all')->where('tag', '[A-Za-z_]+');