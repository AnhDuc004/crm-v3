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

Route::middleware(['auth:api'])->name('api.')->group(function () {
    // Route::get('contract', fn (Request $request) => $request->user())->name('contract');
    Route::get('contract/renewal', 'ContractRenewalsController@index');
    Route::get('contract/renewal/{id}', 'ContractRenewalsController@getListByContract');
    Route::post('contract/renewal/{id}', 'ContractRenewalsController@create');
    Route::get('renewal/{id}', 'ContractRenewalsController@show');
    Route::put('contract/renewal/{id}', 'ContractRenewalsController@update');
    Route::delete('contract/renewal/{id}', 'ContractRenewalsController@destroy');
    //api Contracts
    Route::post('contract/copy/{id}', 'ContractController@copyContract');
    Route::get('contract/countActive', 'ContractController@countActive');
    Route::get('contract/count', 'ContractController@countContractsByType');
    Route::get('contract', 'ContractController@index');
    Route::get('contract/filter', 'ContractController@filterByContract');
    Route::post('contract', 'ContractController@store');
    Route::get('contract/comment/{id}', 'ContractController@getListByComment');
    Route::post('contract/comment/{id}', 'ContractController@createByComment');
    Route::post('contract/customer/{id}', 'ContractController@createByCustomer');
    Route::get('contract/{id}', 'ContractController@show');
    Route::put('contract/{id}', 'ContractController@update');
    Route::delete('contract/{id}', 'ContractController@destroy');
    Route::get('contract/customer/{id}', 'ContractController@getListByCustomer');
    Route::put('contract/comment/{id}', 'ContractController@updateComment');
    Route::delete('contract/comment/{id}', 'ContractController@destroyComment');
    Route::get('contract/statistic/by/type', 'ContractController@statisticContractsByType');
    Route::get('contract/statistic/value/by/type', 'ContractController@statisticContractsValueByType');
    Route::put('contract/by/content/{id}', 'ContractController@contractByContent');
    Route::put('contract/change/signed/{id}', 'ContractController@changeSigned');

    //api ContractType
    Route::get('contractType', 'ContractTypeController@index');
    Route::post('contractType', 'ContractTypeController@store');
    Route::get('contractType/{id}', 'ContractTypeController@show');
    Route::put('contractType/{id}', 'ContractTypeController@update');
    Route::delete('contractType/{id}', 'ContractTypeController@destroy');
});
