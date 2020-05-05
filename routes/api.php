<?php

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

Route::prefix('v1')->group(function () {

    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');

    Route::middleware('auth:sanctum')->get('user', 'Api\AuthController@user');
    Route::middleware('auth:sanctum')->get('token/revoke', 'Api\AuthController@revokeToken');

    // Draft
    Route::group(['prefix' => 'draft/{uuid}/',  'middleware' => 'auth:sanctum'], function () {
        Route::post('/', 'Api\DraftController@getDraft');
        Route::post('bans', 'Api\DraftController@getBans');
        Route::post('picks', 'Api\DraftController@getPicks');
        Route::post('addban', 'Api\DraftController@addBan');
        Route::post('addpick', 'Api\DraftController@addPick');
        Route::post('save', 'Api\DraftController@save');
        Route::post('spectate/save', 'Api\DraftController@saveSpectator');
        Route::get('spectate', 'Api\DraftController@spectate');
    });

    Route::get('maps', 'Api\MapsController@getMaps');
    Route::middleware('auth:sanctum')->get('drafts', 'Api\DraftController@getDrafts');
    Route::middleware('auth:sanctum')->post('drafts/create', 'Api\DraftController@create');

});
