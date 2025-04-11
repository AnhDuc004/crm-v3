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
    // Route::get('expense', fn (Request $request) => $request->user())->name('expense');
    //api Expenses
    Route::get('expenses/year', 'ExpensesController@getListByYear');
    Route::get('expenses/filter', 'ExpensesController@filterByExpense');
    Route::get('expenses/filter/project/{id}', 'ExpensesController@filterExpenseByProject');
    Route::get('expenses/count/{id}', 'ExpensesController@count');
    Route::get('expenses/project/{id}', 'ExpensesController@getListByProject');
    Route::get('expenses/project/year/{id}', 'ExpensesController@getListByYearProject');
    Route::get('expenses/customer/year/{id}', 'ExpensesController@getListByYearCustomer');
    Route::get('expenses', 'ExpensesController@index');
    Route::post('expenses/customer/{id}', 'ExpensesController@createByCustomer');
    Route::post('expenses/project/{id}', 'ExpensesController@createByProject');
    Route::post('expenses', 'ExpensesController@store');
    Route::get('expenses/{id}', 'ExpensesController@show');
    Route::put('expenses/{id}', 'ExpensesController@update');
    Route::delete('expenses/{id}', 'ExpensesController@destroy');
    Route::get('expenses/customer/{id}', 'ExpensesController@getListByCustomer');

    //api Expenses-Categories
    Route::get('expenses-category', 'ExpensesCategoriesController@index');
    Route::post('expenses-category', 'ExpensesCategoriesController@store');
    Route::put('expenses-category/{id}', 'ExpensesCategoriesController@update');
    Route::delete('expenses-category/{id}', 'ExpensesCategoriesController@destroy');
});
