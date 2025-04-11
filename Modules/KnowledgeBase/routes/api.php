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
//     Route::get('knowledgebase', fn (Request $request) => $request->user())->name('knowledgebase');
// });

Route::get('knowledgeBase', 'KnowledgeBaseController@index');
Route::post('knowledgeBase', 'KnowledgeBaseController@store');
Route::get('knowledgeBase/{id}', 'KnowledgeBaseController@show');
Route::put('knowledgeBase/{id}', 'KnowledgeBaseController@update');
Route::delete('knowledgeBase/{id}', 'KnowledgeBaseController@destroy');

//api KnowledgeBaseGroup
Route::get('knowledgeBaseGroup', 'KnowledgeBaseGroupController@index');
Route::post('knowledgeBaseGroup', 'KnowledgeBaseGroupController@store');
Route::get('knowledgeBaseGroup/{id}', 'KnowledgeBaseGroupController@show');
Route::put('knowledgeBaseGroup/{id}', 'KnowledgeBaseGroupController@update');
Route::delete('knowledgeBaseGroup/{id}', 'KnowledgeBaseGroupController@destroy');