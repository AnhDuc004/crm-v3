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
    // Route::get('lead', fn (Request $request) => $request->user())->name('lead');
    //api lead
    Route::post('lead/convert-to-customer/{id}', 'LeadController@convertToCustomer');
    Route::get('lead/countstatus/{id}', 'LeadController@countLeadByStatus');
    Route::get('lead/countsources', 'LeadController@countLeadBySources');
    Route::get('lead', 'LeadController@index');
    Route::post('lead', 'LeadController@store');
    Route::get('lead/{id}', 'LeadController@show');
    Route::put('lead/{id}', 'LeadController@update');
    Route::put('lead/{id}/{status}', 'LeadController@changeStatus');
    Route::delete('lead/{id}', 'LeadController@destroy');

    //api lead-log
    Route::get('lead-activity-log', 'LeadActivityLogController@index');
    Route::post('lead-activity-log', 'LeadActivityLogController@store');
    Route::get('lead-activity-log/{id}', 'LeadActivityLogController@show');
    Route::put('lead-activity-log/{id}', 'LeadActivityLogController@update');
    Route::delete('lead-activity-log/{id}', 'LeadActivityLogController@destroy');
    Route::get('lead-activity-log/lead/{id}', 'LeadActivityLogController@getListByLead');

    //api lead-integration-email
    Route::get('lead-integration-email', 'LeadIntegrationEmailController@index');
    Route::post('lead-integration-email', 'LeadIntegrationEmailController@store');
    Route::get('lead-integration-email/{id}', 'LeadIntegrationEmailController@show');
    Route::put('lead-integration-email/{id}', 'LeadIntegrationEmailController@update');
    Route::delete('lead-integration-email/{id}', 'LeadIntegrationEmailController@destroy');

    //api lead-source
    Route::get('lead-source', 'LeadSourceController@index');
    Route::post('lead-source', 'LeadSourceController@store');
    Route::get('lead-source/{id}', 'LeadSourceController@show');
    Route::put('lead-source/{id}', 'LeadSourceController@update');
    Route::delete('lead-source/{id}', 'LeadSourceController@destroy');

    //api lead-status
    Route::get('lead-status', 'LeadStatusController@index');
    Route::post('lead-status', 'LeadStatusController@store');
    Route::get('lead-status/{id}', 'LeadStatusController@show');
    Route::put('lead-status/{id}', 'LeadStatusController@update');
    Route::delete('lead-status/{id}', 'LeadStatusController@destroy');

    //api lead-email-integration
    Route::get('lead-email-integration', 'LeadEmailIntegrationController@index');
    Route::post('lead-email-integration', 'LeadEmailIntegrationController@store');
    Route::get('lead-email-integration/{id}', 'LeadEmailIntegrationController@show');
    Route::put('lead-email-integration/{id}', 'LeadEmailIntegrationController@update');
    Route::delete('lead-email-integration/{id}', 'LeadEmailIntegrationController@destroy');

    //api WebToLead
    Route::get('webToLead', 'WebToLeadController@index');
    Route::post('webToLead', 'WebToLeadController@store');
    Route::get('webToLead/{id}', 'WebToLeadController@show');
    Route::put('webToLead/{id}', 'WebToLeadController@update');
    Route::delete('webToLead/{id}', 'WebToLeadController@destroy');
});
