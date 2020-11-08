<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {


    return $request->user();
});

Route::any('addadmin', array('middleware' => 'cors', 'uses' => 'LoginModelController@storeAdmin'));
Route::any('login', array('middleware' => 'cors', 'uses' => 'LoginModelController@login'));
Route::any('verifycode', array('middleware' => 'cors', 'uses' => 'LoginModelController@verifyCode'));
Route::any('getdata', array('middleware' => 'cors', 'uses' => 'LoginModelController@getData'));
Route::any('getCountry', array('middleware' => 'cors', 'uses' => 'LoginModelController@get_countries'));
Route::any('addGroup',array('middleware' => 'cors', 'uses' => 'LoginModelController@storeGroup'));
Route::any('allmembers',array('middleware' => 'cors', 'uses' => 'LoginModelController@allMembers'));
Route::any('allgroups',array('middleware' => 'cors', 'uses' => 'LoginModelController@allGroups'));
Route::any('delete',array('middleware' => 'cors', 'uses' => 'Controller@deleteR'));









// Route::post('addadmin', 'LoginModelController@storeAdmin');
// Route::post('login', 'LoginModelController@login');
// Route::get('getdata', 'LoginModelController@getData');
// Route::post('verifycode', 'LoginModelController@verifyCode');


// Route::get('getCountry','LoginModelController@get_countries');