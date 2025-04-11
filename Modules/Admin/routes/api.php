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
    // api Currencies
    Route::apiResource('currencies', 'CurrencyController');

    //api country
    Route::apiResource('country', 'CountryController');


    //api Staff
    Route::get('staff', 'StaffController@index');
    Route::post('staff', 'StaffController@store');
    Route::get('staff/{id}', 'StaffController@show');
    Route::put('staff/{id}', 'StaffController@update');
    Route::delete('staff/{id}', 'StaffController@destroy');
    Route::put('staff/{id}/toggle-active', 'StaffController@toggleActive');
    Route::get('staff/list-by-task', 'StaffController@getListByTask');
    Route::get('staff/list-by-ticket', 'StaffController@getListByTicket');
    Route::get('staff/list-by-proposal', 'StaffController@getListByProposal');
    Route::get('staff/list-by-estimate/{staffId}', 'StaffController@getListByEstimate');
    Route::get('staff/list-by-invoice/{staffId}', 'StaffController@getListByInvoice');
    Route::post('/staff/{id}/profile-image', 'StaffController@updateProfileImage');


    //api Department
    Route::get('department', 'DepartmentController@index');
    Route::post('department', 'DepartmentController@store');
    Route::get('department/{id}', 'DepartmentController@show');
    Route::put('department/{id}', 'DepartmentController@update');
    Route::delete('department/{id}', 'DepartmentController@destroy');

    //api Comment
    // Route::get('comment', 'CommentController@index');
    // Route::post('comment', 'CommentController@store');
    // Route::get('comment/{id}', 'CommentController@show');
    // Route::put('comment/{id}', 'CommentController@update');
    // Route::delete('comment/{id}', 'CommentController@destroy');

    //api File
    // Route::get('file', 'FileController@index');
    // Route::post('file', 'FileController@store');
    // Route::get('file/{id}', 'FileController@show');
    // Route::put('file/{id}', 'FileController@update');
    // Route::delete('file/{id}', 'FileController@destroy');
    // Route::get('file/customer/{id}', 'FileController@getListByCustomer');
    // Route::get('file/contract/{id}', 'FileController@getListByContract');
    // Route::get('file/lead/{id}', 'FileController@getListByLead');
    // Route::post('file/lead/{id}', 'FileController@uploadFileByLead');
    // Route::post('file/customer/{id}', 'FileController@uploadFileByCustomer');
    // Route::post('file/contract/{id}', 'FileController@uploadFileByContract');
    // Route::post('file/proposal/{id}', 'FileController@uploadFileByProposal');
    // Route::get('file/proposal/{id}', 'FileController@getListByProposal');
    // Route::post('file/estimate/{id}', 'FileController@uploadFileByEstimate');
    // Route::get('file/estimate/{id}', 'FileController@getListByEstimate');
    // Route::post('file/invoice/{id}', 'FileController@uploadFileByInvoice');
    // Route::get('file/invoice/{id}', 'FileController@getListByInvoice');
    Route::post('file/task/{id}', 'FileController@uploadFileByTask');
    // Route::get('file/task/{id}', 'FileController@getListByTask');
    // Route::put('file/changeVisibleToCustomer/{id}', 'FileController@changeVisibleToCustomer');
    // Route::get('file/download/{id}', 'FileController@download');


    //api Vault
    // Route::get('vault', 'VaultController@index');
    // Route::post('vault/customer/{id}', 'VaultController@createByCustomer');
    // Route::get('vault/{id}', 'VaultController@show');
    // Route::put('vault/{id}', 'VaultController@update');
    // Route::delete('vault/{id}', 'VaultController@destroy');
    // Route::get('vault/customer/{id}', 'VaultController@getListByCustomer');


    //api Contact
    // Route::get('contact', 'ContactController@index');
    // Route::get('contact/customer/{id}', 'ContactController@getListByCustomer');
    // Route::post('contact/customer/{id}', 'ContactController@store');
    // Route::post('contact/{id}', 'ContactController@update');
    // Route::put('contact/active/{id}', 'ContactController@changeActive');
    // Route::delete('contact/{id}', 'ContactController@destroy');

    //api Announcements
    // Route::get('announcements', 'AnnouncementsController@index');
    // Route::post('announcements', 'AnnouncementsController@store');
    // Route::get('announcements/{id}', 'AnnouncementsController@show');
    // Route::put('announcements/{id}', 'AnnouncementsController@update');
    // Route::delete('announcements/{id}', 'AnnouncementsController@destroy');

    // //api Tax
    Route::get('tax', 'TaxController@index');
    Route::post('tax', 'TaxController@store');
    Route::get('tax/{id}', 'TaxController@show');
    Route::put('tax/{id}', 'TaxController@update');
    Route::delete('tax/{id}', 'TaxController@destroy');

    // //api SpamFilter
    // Route::get('spamFilter', 'SpamFilterController@index');
    // Route::post('spamFilter', 'SpamFilterController@store');
    // Route::get('spamFilter/{id}', 'SpamFilterController@show');
    // Route::put('spamFilter/{id}', 'SpamFilterController@update');
    // Route::delete('spamFilter/{id}', 'SpamFilterController@destroy');

    // //api Service
    // Route::get('service', 'ServiceController@index');
    // Route::post('service', 'ServiceController@store');
    // Route::get('service/{id}', 'ServiceController@show');
    // Route::put('service/{id}', 'ServiceController@update');
    // Route::delete('service/{id}', 'ServiceController@destroy');

    //api Department
    Route::get('department', 'DepartmentController@index');
    Route::post('department', 'DepartmentController@store');
    Route::get('department/{id}', 'DepartmentController@show');
    Route::put('department/{id}', 'DepartmentController@update');
    Route::delete('department/{id}', 'DepartmentController@destroy');

    //api Module
    // Route::get('module', 'ModuleController@index');
    // Route::post('module', 'ModuleController@store');
    // Route::get('module/{id}', 'ModuleController@show');
    // Route::put('module/{id}', 'ModuleController@update');
    // Route::delete('module/{id}', 'ModuleController@destroy');

    // //api CustomField
    // Route::get('customField', 'CustomFieldController@index');
    // Route::post('customField', 'CustomFieldController@store');
    // Route::get('customField/{id}', 'CustomFieldController@show');
    // Route::get('customField/fieldto/{id}', 'CustomFieldController@getByName');
    // Route::put('customField/{id}', 'CustomFieldController@update');
    // Route::delete('customField/{id}', 'CustomFieldController@destroy');
    // Route::put('customField/active/{id}', 'CustomFieldController@changeActive');

    // //api Notification
    // Route::get('notification', 'NotificationController@index');
    // Route::post('notification', 'NotificationController@store');
    // Route::get('notification/{id}', 'NotificationController@show');
    // Route::put('notification/{id}', 'NotificationController@update');
    // Route::delete('notification/{id}', 'NotificationController@destroy');
    // Route::put('notification/isRead/{id}', 'NotificationController@isRead');

    // api Option
    // Route::get('option', 'OptionController@index');
    // Route::post('option', 'OptionController@store');
    // Route::put('option/update', 'OptionController@update');
    // Route::delete('option/{id}', 'OptionController@destroy');
    // });
});
