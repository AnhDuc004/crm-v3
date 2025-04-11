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
    // Route::get('sale', fn (Request $request) => $request->user())->name('sale');
    //api Proposal
    Route::get('proposal/filter', 'ProposalController@filterByProposal');
    Route::get('proposal/count', 'ProposalController@count');
    Route::get('proposal', 'ProposalController@index');
    Route::post('proposal', 'ProposalController@store');
    Route::get('proposal/{id}', 'ProposalController@show');
    Route::put('proposal/{id}', 'ProposalController@update');
    Route::delete('proposal/{id}', 'ProposalController@destroy');
    Route::put('proposal/{id}/{status}', 'ProposalController@changeStatus');
    Route::get('proposal/customer/{id}', 'ProposalController@getListByCustomer');
    Route::post('proposal/customer/{id}', 'ProposalController@createByCustomer');
    Route::get('proposal/lead/{id}', 'ProposalController@getListByLead');
    Route::post('proposal/lead/{id}', 'ProposalController@createByLead');
    Route::get('proposal/itembale/{id}', 'ProposalController@findItemable');
    Route::post('proposal', 'ProposalController@store');
    Route::post('proposal/copy/{id}', 'ProposalController@copyData'); 
    //api Proposal comment
    Route::get('proposalComment', 'ProposalCommentController@index');
    Route::post('proposalComment/{id}', 'ProposalCommentController@store');
    Route::put('proposalComment/{id}', 'ProposalCommentController@update');
    Route::delete('proposalComment/{id}', 'ProposalCommentController@destroy');

    //api CreditNote
    Route::get('creditNotes/filter', 'CreditNotesController@filterByCreditNote');
    Route::get('creditNotes', 'CreditNotesController@index');
    Route::get('creditNotes/project/{id}', 'CreditNotesController@getListByProject');
    Route::get('creditNotes/customer/{id}', 'CreditNotesController@getListByCustomer');
    Route::post('creditNotes', 'CreditNotesController@store');
    Route::get('creditNotes/{id}', 'CreditNotesController@show');
    Route::put('creditNotes/{id}', 'CreditNotesController@update');
    Route::delete('creditNotes/{id}', 'CreditNotesController@destroy');
    Route::post('creditNotes/customer/{id}', 'CreditNotesController@createByCustomer');

    //api CreditNoteRefund
    Route::get('creditNotes/fund/{id}', 'CreditNotesRefundsController@getListByCreditNote');
    Route::post('creditNotes/fund/{id}', 'CreditNotesRefundsController@createByCreditNote');
    Route::put('creditNotesRefunds/{id}', 'CreditNotesRefundsController@update');
    Route::delete('creditNotesRefunds/{id}', 'CreditNotesRefundsController@destroy');

    //api Credits
    Route::get('credits/notes/{id}', 'CreditsController@getListByCreditNote');
    Route::post('credits/notes/{id}', 'CreditsController@createByCreditNote');
    Route::put('credits/{id}', 'CreditsController@update');
    Route::delete('credits/{id}', 'CreditsController@destroy');

    //api Itemable
    Route::get('itemable', 'ItemableController@index');
    Route::get('itemable/invoice/{id}', 'ItemableController@showInvoice');
    Route::post('itemable', 'ItemableController@store');
    Route::get('itemable/{id}', 'ItemableController@show');
    Route::put('itemable/{id}', 'ItemableController@update');
    Route::delete('itemable/{id}', 'ItemableController@destroy');

    //api Estimate
    Route::get('estimate/filter/project/{id}', 'EstimateController@filterEstimateByProject');
    Route::get('estimate/filter', 'EstimateController@filterByEstimate');
    Route::get('estimate/count', 'EstimateController@countByStatus');
    Route::get('estimate/customer/{id}', 'EstimateController@getListByCustomer');
    Route::get('estimate/count/{id}', 'EstimateController@countByCustomer');
    Route::get('estimate/project/{id}', 'EstimateController@getListByProject');
    Route::get('estimate/project/year/{id}', 'EstimateController@getListByYearProject');
    Route::get('estimate/customer/year/{id}', 'EstimateController@getListByYearCustomer');
    Route::get('estimate/year', 'EstimateController@getListByYear');
    Route::get('estimate', 'EstimateController@index');
    Route::post('estimate/customer/{id}', 'EstimateController@createByCustomer');
    Route::post('estimate', 'EstimateController@store');
    Route::get('estimate/{id}', 'EstimateController@show');
    Route::put('estimate/{id}', 'EstimateController@update');
    Route::delete('estimate/{id}', 'EstimateController@destroy');
    Route::get('estimate/itemable/{id}', 'EstimateController@getListByItemable');
    Route::get('estimate/project/total/{id}', 'EstimateController@countEstimateByProject');
    Route::put('estimate/changeStatus/{id}', 'EstimateController@changeStatus');
    Route::post('estimate/copy/{id}', 'EstimateController@copyData');
    Route::post('proposal/convertEs/{id}', 'EstimateController@convertProposalToEstimaste');

    //api Invoice
    Route::get('invoice/year', 'InvoiceController@getListByYear');
    Route::get('invoice/filter', 'InvoiceController@filterByInvoice');
    Route::get('invoice/filter/project/{id}', 'InvoiceController@filterInvoiceByProject');
    Route::get('invoice', 'InvoiceController@index');
    Route::get('invoice/{id}', 'InvoiceController@show');
    Route::get('invoice/customer/{id}', 'InvoiceController@getListByCustomer');
    Route::get('invoice/project/{id}', 'InvoiceController@getListByProject');
    Route::get('invoice/project/year/{id}', 'InvoiceController@getListByYearProject');
    Route::get('invoice/customer/year/{id}', 'InvoiceController@getListByYearCustomer');
    Route::post('invoice/customer/{id}', 'InvoiceController@createByCustomer');
    Route::post('invoice', 'InvoiceController@create');
    Route::put('invoice/{id}', 'InvoiceController@update');
    Route::delete('invoice/{id}', 'InvoiceController@destroy');
    Route::get('invoice/project/total/{id}', 'InvoiceController@countInvoiceByProject');
    Route::get('invoice/recurring', 'InvoiceController@getListRecuringInvoice');
    Route::post('invoice/payment/{id}', 'InvoiceController@payment');
    Route::get('invoice/sameCustomer/{id}', 'InvoiceController@invoiceWithCustomers');
    Route::post('estimate/convertInvoice/{id}', 'InvoiceController@convertEstimateToInvoice');
    Route::post('proposal/convertInvoice/{id}', 'InvoiceController@convertProposalToInvoice');
    Route::post('invoice/copy/{id}', 'InvoiceController@copyData');
    Route::get('invoice/customer/count/{id}', 'InvoiceController@countInvoiceByCustomer');

    //api InvoicePayment
    Route::get('payment', 'PaymentController@index');
    Route::get('payment/{id}', 'PaymentController@show');
    Route::get('payment/customer/{id}', 'PaymentController@getListByCustomer');
    Route::post('payment/customer/{id}', 'PaymentController@createByCustomer');
    Route::put('payment/{id}', 'PaymentController@update');
    Route::delete('payment/{id}', 'PaymentController@destroy');

    //api PaymentMode
    Route::get('paymentModes', 'PaymentModesController@index');
    Route::post('paymentModes', 'PaymentModesController@store');
    Route::get('paymentModes/{id}', 'PaymentModesController@show');
    Route::put('paymentModes/{id}', 'PaymentModesController@update');
    Route::delete('paymentModes/{id}', 'PaymentModesController@destroy');
    Route::put('paymentModes/{id}/toggle-active', 'PaymentModesController@changeActive');

    //api items
    Route::get('item', 'ItemController@index');
    Route::post('item', 'ItemController@store');
    Route::get('item/{id}', 'ItemController@show');
    Route::put('item/{id}', 'ItemController@update');
    Route::delete('item/{id}', 'ItemController@destroy');

    //api item-group
    Route::get('item-group', 'ItemGroupController@index');
    Route::post('item-group', 'ItemGroupController@store');
    Route::put('item-group/{id}', 'ItemGroupController@update');
    Route::delete('item-group/{id}', 'ItemGroupController@destroy');

    // api taxes
    Route::apiResource('taxes', 'TaxesController');

    //api sale-activity
    Route::get('sale/estimate/activity/{id}', 'SaleActivityController@getSaleActivityByEstimate');
    Route::get('sale/invoice/activity/{id}', 'SaleActivityController@getSaleActivityByInvoice');
    Route::delete('sale/activity/{id}', 'EstimateController@destroy');
});
