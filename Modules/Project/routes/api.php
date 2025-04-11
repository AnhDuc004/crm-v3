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
    // Route::get('project', fn (Request $request) => $request->user())->name('project');
    //api Project
    Route::post('projects/bulk-action', 'ProjectController@bulkAction');
    Route::get('project/customer/count/{id}', 'ProjectController@countByCustomer');
    Route::get('project', 'ProjectController@index');
    Route::post('project', 'ProjectController@store');
    Route::get('project/{id}', 'ProjectController@show');
    Route::put('project/{id}', 'ProjectController@update');
    Route::delete('project/{id}', 'ProjectController@destroy');
    Route::get('project/customer/{id}', 'ProjectController@getListByCustomer');
    Route::post('project/customer/{id}', 'ProjectController@createByCustomer');
    Route::get('project/contact/{id}', 'ProjectController@getContactByProject');

    Route::get('project/count/status', 'ProjectController@countByStatus');
    Route::get('project/task/{id}', 'ProjectController@countOverview');
    Route::get('project/count/{id}', 'ProjectController@countDayLeft');
    Route::post('project/copy/{projectId}', 'ProjectController@copy');
    Route::get('project/staff/{id}', 'ProjectController@getListByStaff');
    Route::post('project/staff/{id}', 'ProjectController@addMember');
    Route::delete('project/{project_id}/staff/{staff_id}', 'ProjectController@deleteMember');

    //api project-discussions
    Route::get('project/discussions/{projectid}', 'ProjectDiscussionsController@listByProject');
    Route::post('project/discussions/{projectid}', 'ProjectDiscussionsController@store');
    Route::put('project/discussions/{id}', 'ProjectDiscussionsController@update');
    Route::delete('project/discussions/{id}', 'ProjectDiscussionsController@destroy');

    //api project-milestone
    Route::get('project/milestone/getall', 'ProjectMilestoneController@index');
    Route::post('project/milestone/{projectid}', 'ProjectMilestoneController@store');
    Route::delete('project/milestone/{id}', 'ProjectMilestoneController@destroy');
    Route::get('project/milestone/{projectid}', 'ProjectMilestoneController@listByProject');
    Route::put('project/milestone/{id}', 'ProjectMilestoneController@update');

    //api project-tickets
    Route::get('project/tickets/count/{id}', 'ProjectTicketsController@count');
    Route::get('project/tickets/getall', 'ProjectTicketsController@index');
    Route::get('project/tickets/{projectid}', 'ProjectTicketsController@listByProject');
    Route::post('project/tickets/{projectid}', 'ProjectTicketsController@store');
    Route::delete('project/tickets/{id}', 'ProjectTicketsController@destroy');
    Route::put('project/tickets/{id}', 'ProjectTicketsController@update');

    //api project-files
    Route::get('project/files/getall', 'ProjectFilesController@index');
    Route::post('project/files/{projectid}', 'ProjectFilesController@store');
    Route::delete('project/files/{id}', 'ProjectFilesController@destroy');
    Route::put('project/files/{id}', 'ProjectFilesController@update');
    Route::post('file/project/{id}', 'ProjectFilesController@uploadFileByProject');
    Route::get('file/project/{id}', 'ProjectFilesController@getListByProject');
    Route::put('file/changeVisibleToCustomer/{id}', 'ProjectFilesController@changeVisibleToCustomer');
    Route::get('file/project/download/{id}', 'ProjectFilesController@download');

    //api project-activity
    Route::get('project/activity', 'ProjectActivityController@index');
    Route::get('project/activity/{id}', 'ProjectActivityController@getListByProject');
    Route::delete('project/activity/{id}', 'ProjectActivityController@delete');
    //api project-notes
    Route::get('project/notes/getall', 'ProjectNotesController@index');
    Route::get('project/notes/{id}', 'ProjectNotesController@getListByProject');
    Route::post('project/notes/{projectid}', 'ProjectNotesController@store');
    Route::delete('project/notes/{id}', 'ProjectNotesController@destroy');
    Route::put('project/notes/{id}', 'ProjectNotesController@update');
});
