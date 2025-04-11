<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Customer\App\Http\Controllers\FileController;

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

Route::middleware(['auth:api'])
    ->name('api.')
    ->group(function () {
        // Route::get('customer', fn (Request $request) => $request->user())->name('customer');
        //api tag
        Route::get('tag', 'TagController@index');
        Route::post('tag', 'TagController@store');
        Route::get('tag/{id}', 'TagController@show');
        Route::put('tag/{id}', 'TagController@update');
        Route::delete('tag/{id}', 'TagController@destroy');

        //api note
        Route::get('note/customer/{id}', 'NoteController@getListByCustomer');
        Route::get('note/{id}', 'NoteController@show');
        Route::get('note', 'NoteController@index');
        Route::post('note/customer/{id}', 'NoteController@createByCustomer');
        Route::put('note/{id}', 'NoteController@update');
        Route::delete('note/{id}', 'NoteController@destroy');
        Route::get('note/lead/{id}', 'NoteController@getByLead');
        Route::post('note/lead/{id}', 'NoteController@createByLead');
        Route::get('note/proposal/{id}', 'NoteController@getByProposal');
        Route::post('note/proposal/{id}', 'NoteController@createByProposal');
        Route::get('note/invoice/{id}', 'NoteController@getByInvoice');
        Route::post('note/invoice/{id}', 'NoteController@createByInvoice');
        Route::get('note/staff/{id}', 'NoteController@getByStaff');
        Route::post('note/staff/{id}', 'NoteController@createByStaff');
        //api note-estimate
        Route::get('note/estimate/{id}', 'NoteController@getByEstimaste');
        Route::post('note/estimate/{id}', 'NoteController@createByEstimaste');
        Route::get('note/contract/{id}', 'NoteController@getListByContract');
        Route::post('note/contract/{id}', 'NoteController@createByContract');
        Route::get('note/ticket/{id}', 'NoteController@getListByTicket');
        Route::post('note/ticket/{id}', 'NoteController@createByTicket');

        //api customer
        Route::get('customer/inactive', 'CustomerController@getInactiveCustomers');
        Route::post('customer/bulk-action', 'CustomerController@bulkAction');
        Route::get('customer', 'CustomerController@index');
        Route::get('customer/listSelect', 'CustomerController@listSelect');
        Route::get('customer/count', 'CustomerController@count');
        Route::get('customer/statement/{id}', 'CustomerController@statement');
        Route::get('customer/filter', 'CustomerController@filterByCustomer');
        Route::post('customer', 'CustomerController@store');
        Route::get('customer/{id}', 'CustomerController@show');
        Route::put('customer/{id}', 'CustomerController@update');
        Route::delete('customer/{id}', 'CustomerController@destroy');
        Route::put('customer/{id}/toggle-active', 'CustomerController@toggleActive');

        //api Customer-group
        Route::apiResource('customer-group', 'CustomerGroupController');

        // api Customer-Admin
        Route::get('customer-admin/{id}', 'CustomerAdminController@index');
        Route::post('customer-admin/{id}', 'CustomerAdminController@store');
        Route::put('customer-admin/{id}', 'CustomerAdminController@update');
        Route::delete('customer-admin/{id}', 'CustomerAdminController@destroy');

        //api Currencies
        //Route::apiResource('currencies', 'CurrenciesController');


        //api Comment
        // Route::get('comment', 'CommentController@index');
        // Route::post('comment', 'CommentController@store');
        // Route::get('comment/{id}', 'CommentController@show');
        // Route::put('comment/{id}', 'CommentController@update');
        // Route::delete('comment/{id}', 'CommentController@destroy');

        //api File
        Route::get('file', 'FileController@index');
        Route::post('file', 'FileController@store');
        Route::get('file/{id}', 'FileController@show');
        Route::put('file/{id}', 'FileController@update');
        Route::delete('file/{id}', 'FileController@destroy');
        Route::get('file/customer/{id}', 'FileController@getListByCustomer');
        Route::get('file/contract/{id}', 'FileController@getListByContract');
        Route::get('file/lead/{id}', 'FileController@getListByLead');
        Route::post('file/lead/{id}', 'FileController@uploadFileByLead');
        Route::post('file/customer/{id}', 'FileController@uploadFileByCustomer');
        Route::post('file/contract/{id}', 'FileController@uploadFileByContract');
        Route::post('file/proposal/{id}', 'FileController@uploadFileByProposal');
        Route::get('file/proposal/{id}', 'FileController@getListByProposal');
        Route::post('file/estimate/{id}', 'FileController@uploadFileByEstimate');
        Route::get('file/estimate/{id}', 'FileController@getListByEstimate');
        Route::post('file/invoice/{id}', 'FileController@uploadFileByInvoice');
        Route::get('file/invoice/{id}', 'FileController@getListByInvoice');
        Route::post('file/task/{id}', 'FileController@uploadFileByTask');
        Route::get('file/task/{id}', 'FileController@getListByTask');
        Route::put('file/changeVisibleToCustomer/{id}', 'FileController@changeVisibleToCustomer');
        Route::get('file/download/{id}', 'FileController@download');
        // Route::post('file/contract/{id}', [FileController::class, 'uploadFileByContract']);

        //api Vault
        Route::get('vault', 'VaultController@index');
        Route::post('vault/customer/{id}', 'VaultController@createByCustomer');
        Route::get('vault/{id}', 'VaultController@show');
        Route::put('vault/{id}', 'VaultController@update');
        Route::delete('vault/{id}', 'VaultController@destroy');
        Route::get('vault/customer/{id}', 'VaultController@getListByCustomer');

        //api Contact
        Route::get('contact', 'ContactController@index');
        Route::get('contact/customer/{id}', 'ContactController@getListByCustomer');
        Route::post('contact/customer/{id}', 'ContactController@store');
        Route::post('contact/{id}', 'ContactController@update');
        Route::put('contact/{id}/toggle-active', 'ContactController@toggleActive');
        Route::delete('contact/{id}', 'ContactController@destroy');

        //api Announcements
        // Route::get('announcements', 'AnnouncementsController@index');
        // Route::post('announcements', 'AnnouncementsController@store');
        // Route::get('announcements/{id}', 'AnnouncementsController@show');
        // Route::put('announcements/{id}', 'AnnouncementsController@update');
        // Route::delete('announcements/{id}', 'AnnouncementsController@destroy');

        // //api Tax
        // Route::get('tax', 'TaxController@index');
        // Route::post('tax', 'TaxController@store');
        // Route::get('tax/{id}', 'TaxController@show');
        // Route::put('tax/{id}', 'TaxController@update');
        // Route::delete('tax/{id}', 'TaxController@destroy');

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

        // //api Department
        // Route::get('department', 'DepartmentController@index');
        // Route::post('department', 'DepartmentController@store');
        // Route::get('department/{id}', 'DepartmentController@show');
        // Route::put('department/{id}', 'DepartmentController@update');
        // Route::delete('department/{id}', 'DepartmentController@destroy');

        //api Module
        Route::get('module', 'ModuleController@index');
        Route::post('module', 'ModuleController@store');
        Route::get('module/{id}', 'ModuleController@show');
        Route::put('module/{id}', 'ModuleController@update');
        Route::delete('module/{id}', 'ModuleController@destroy');

        //api CustomField
        Route::get('customField', 'CustomFieldController@index');
        Route::post('customField', 'CustomFieldController@store');
        Route::get('customField/{id}', 'CustomFieldController@show');
        Route::get('customField/fieldto/{id}', 'CustomFieldController@getByName');
        Route::put('customField/{id}', 'CustomFieldController@update');
        Route::delete('customField/{id}', 'CustomFieldController@destroy');
        Route::put('customField/{id}/toggle-active', 'CustomFieldController@toggleActive');

        //api Notification
        Route::get('notification', 'NotificationController@index');
        Route::post('notification', 'NotificationController@store');
        Route::get('notification/{id}', 'NotificationController@show');
        Route::put('notification/{id}', 'NotificationController@update');
        Route::delete('notification/{id}', 'NotificationController@destroy');
        Route::put('notification/isRead/{id}', 'NotificationController@isRead');

        // api Option
        Route::get('option', 'OptionController@index');
        Route::post('option', 'OptionController@store');
        Route::put('option/update', 'OptionController@update');
        Route::delete('option/{id}', 'OptionController@destroy');
    });
