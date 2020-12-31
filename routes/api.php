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

Route::get('summary', 'ReportController@summaryProvince');
Route::get('summary/{split}', 'ReportController@SummaryProvince')->where('split', 'split');
Route::get('summary/split/hr', 'ReportController@SummaryHealthRegion');

Route::get('provinces', 'ProvinceController@list');
Route::get('province/{province}/regions', 'ProvinceController@healthRegions')->where('province', '[A-Za-z_]+');

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

// ADMIN (manage/)
Route::middleware('guest')->group(function () {
    Route::post('manage/login', 'AuthController@login')->name('login');
    Route::post('manage/refresh-token', 'AuthController@refreshToken')->name('refreshToken');
});

Route::middleware('auth:api')->group(function () {
    Route::post('manage/logout', 'AuthController@logout')->name('logout');
    Route::get('manage/user', 'AuthController@user');
    
    Route::get('manage/report/{province}', 'ManageController@getReports')->where('province', '[A-Za-z_]+');
    Route::post('manage/report', 'ManageController@saveReports');
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('manage/users', 'UserController@getUsers');
    Route::get('manage/users/{id}', 'UserController@getUser')->where('id', '[\d]+');
    Route::post('manage/users/{id}', 'UserController@updateUser')->where('id', '[\d]+');
    Route::post('manage/users/create', 'UserController@createUser');
    Route::post('manage/cache/clear', 'ManageController@clearCache');
});