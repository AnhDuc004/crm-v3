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

// Route::middleware(['auth:api'])->name('api.')->group(function () {
//     Route::get('subscription', fn (Request $request) => $request->user())->name('subscription');
// });

Route::get('subscription', 'SubscriptionController@index');
Route::post('subscription/customer/{id}', 'SubscriptionController@createByCustomer');
Route::get('subscription/{id}', 'SubscriptionController@show');
Route::put('subscription/{id}', 'SubscriptionController@update');
Route::delete('subscription/{id}', 'SubscriptionController@destroy');
Route::get('subscription/customer/{id}', 'SubscriptionController@findCustomer');
Route::get('subscription/project/{id}', 'SubscriptionController@getByProject');
Route::get('subscription/countNotSubscribed', 'SubscriptionController@countNotSubscribed');
Route::get('subscription/countActive', 'SubscriptionController@countActive');
Route::get('subscription/countFuture', 'SubscriptionController@countFuture');
Route::get('subscription/countPastDue', 'SubscriptionController@countPastDue');
Route::get('subscription/countPaid', 'SubscriptionController@countPaid');
Route::get('subscription/countIncomplete', 'SubscriptionController@countIncomplete');
Route::get('subscription/countCanceled', 'SubscriptionController@countCanceled');
Route::get('subscription/countIncompleteExpired', 'SubscriptionController@countIncompleteExpired');