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
    // Route::get('support', fn (Request $request) => $request->user())->name('support');
    //api Ticket
    Route::get('ticket/filter', 'TicketController@filterByTicket');
    Route::get('ticket', 'TicketController@index');
    Route::get('ticket/count', 'TicketController@count');
    Route::get('ticket/project/count/{project_id}', 'TicketController@countByProject');
    Route::post('ticket', 'TicketController@create');
    Route::post('ticket/customer/{id}', 'TicketController@createByCustomer');
    Route::get('ticket/{id}', 'TicketController@show');
    Route::post('ticket/{id}', 'TicketController@update');
    Route::delete('ticket/{id}', 'TicketController@destroy');
    Route::get('ticket/customer/{id}', 'TicketController@getListByCustomer');
    Route::get('ticket/project/{id}', 'TicketController@getListByProject');
    Route::post('ticket/project/{id}', 'TicketController@createByProject');

    //api TicketsStatus
    Route::get('ticketsStatus', 'TicketsStatusController@index');
    Route::post('ticketsStatus', 'TicketsStatusController@store');
    Route::get('ticketsStatus/{id}', 'TicketsStatusController@show');
    Route::put('ticketsStatus/{id}', 'TicketsStatusController@update');
    Route::delete('ticketsStatus/{id}', 'TicketsStatusController@destroy');

    //api TicketsPriority
    Route::get('ticketsPriority', 'TicketsPriorityController@index');
    Route::post('ticketsPriority', 'TicketsPriorityController@store');
    Route::get('ticketsPriority/{id}', 'TicketsPriorityController@show');
    Route::put('ticketsPriority/{id}', 'TicketsPriorityController@update');
    Route::delete('ticketsPriority/{id}', 'TicketsPriorityController@destroy');

    //api PredefinedReplies
    Route::get('predefinedReplies', 'PredefinedRepliesController@index');
    Route::post('predefinedReplies', 'PredefinedRepliesController@store');
    Route::get('predefinedReplies/{id}', 'PredefinedRepliesController@show');
    Route::put('predefinedReplies/{id}', 'PredefinedRepliesController@update');
    Route::delete('predefinedReplies/{id}', 'PredefinedRepliesController@destroy');

    //api Service
    Route::get('service', 'ServiceController@index');
    Route::post('service', 'ServiceController@store');
    Route::get('service/{id}', 'ServiceController@show');
    Route::put('service/{id}', 'ServiceController@update');
    Route::delete('service/{id}', 'ServiceController@destroy');

    //api Spamfilter
    Route::get('spamfilter', 'SpamFilterController@index');
    Route::post('spamfilter', 'SpamFilterController@store');
    Route::get('spamfilter/{id}', 'SpamFilterController@show');
    Route::put('spamfilter/{id}', 'SpamFilterController@update');
    Route::delete('spamfilter/{id}', 'SpamFilterController@destroy');
    Route::post('spamfilter/lead', 'SpamFilterController@createByLead');
});
