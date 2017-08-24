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
// */
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: VKAPP-API-TOKEN, Origin, X-Requested-With, Content-Type, Accept, X-Auth-Token, X-CSRF-TOKEN, Access-Control-Allow-Origin, Access-Control-Allow-Headers, Access-Control-Allow-Methods");
// header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
// Api version 1
Route::group(['middleware' => 'api', 'prefix' => 'v.1'], function(){

    // PBX Server Extension
    Route::match(['options', 'get'], '/extensions', 'ExtensionApiController@getExtensionsList')->name('api.get_all_extension')->middleware('apitoken');
    Route::match(['options', 'get'], '/extension/{ext_number}', 'ExtensionApiController@checkExtension')->name('api.check_extension')->middleware('apitoken');
    Route::match(['options', 'get'],'/get-extension', 'ExtensionApiController@getExtension')->name('api.get_extension')->middleware('apitoken');
    Route::match(['options', 'post'],'/trigger-extension', 'ExtensionApiController@triggerExtension')->name('api.trigger_extensions')->middleware('apitoken');
});

