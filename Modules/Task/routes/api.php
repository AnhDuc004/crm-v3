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
    // Route::get('task', fn (Request $request) => $request->user())->name('task');
    Route::get('task/project/count/{id}', 'TaskController@countByProject');
    Route::get('task/invoice/{id}', 'TaskController@getListByInvoice');
    Route::get('task/filter', 'TaskController@filterByTask');
    Route::get('task', 'TaskController@index');
    Route::get('task/filter/project/{id}', 'TaskController@filterTaskByProject');
    Route::get('project/{projectId}/task', 'TaskController@filterTaskByProjectId');
    Route::get('task/filter/lead/{id}', 'TaskController@filterTaskByLead');
    Route::get('task/filter/contract/{id}', 'TaskController@filterTaskByContract');
    Route::get('task/filter/ticket/{id}', 'TaskController@filterTaskByTicket');
    Route::get('task/filter/customer/{id}', 'TaskController@filterTaskByCustomer');
    Route::get('task/filter/expense/{id}', 'TaskController@filterTaskByExpense');
    Route::get('task/filter/invoice/{id}', 'TaskController@filterTaskByInvoice');
    Route::get('task/filter/proposal/{id}', 'TaskController@filterTaskByProposal');
    Route::get('task/milestones/{id}', 'TaskController@listTaskByMilestones');
    Route::post('task/bulkAction', 'TaskController@bulkAction');
    Route::get('task/current-week', 'TaskController@getTasksInCurrentWeek');
    Route::get('task/has-tasks-by-status', 'TaskController@countTasksByStatus');
    Route::get('/task-summary-by-project', 'TaskController@taskSummaryByProject');
    Route::get('/task-summary-by-customer', 'TaskController@taskSummaryByCustomer');

    // Route::get('task/user', 'TaskController@listByUser')->middleware('role:Staff');
    Route::get('task/count', 'TaskController@count');
    Route::post('task', 'TaskController@store');
    Route::get('task/{id}', 'TaskController@show');
    Route::put('task/{id}', 'TaskController@update');
    Route::delete('task/{id}', 'TaskController@destroy');

    Route::put('task/status/{id}', 'TaskController@changeStatus');
    Route::put('task/priority/{id}', 'TaskController@changePriority');
    Route::get('task/customer/{id}', 'TaskController@getListByCustomer');
    Route::get('task/expense/{id}', 'TaskController@getListByExpense');
    Route::get('task/project/{project_id}', 'TaskController@listByProject');
    Route::get('task/staff/{project_id}/{task_id}', 'TaskController@listByStaff');
    Route::get('task/contract/{id}', 'TaskController@getListByContract');
    Route::get('task/proposal/{id}', 'TaskController@getListByProposal');
    Route::get('task/ticket/{id}', 'TaskController@getListByTicket');

    Route::post('task/checklist/{id}', 'TaskController@addChecklist');
    Route::delete('task/checklist/{id}', 'TaskController@deleteChecklist');

    Route::post('task/comment/{id}', 'TaskController@addComment');
    Route::put('task/comment/{id}', 'TaskController@updateComment');
    Route::delete('task/comment/{id}', 'TaskController@deleteComment');

    Route::post('task/reminder/{id}', 'TaskController@addReminder');
    Route::put('task/reminder/{id}', 'TaskController@updateReminder');
    Route::delete('task/reminder/{id}', 'TaskController@deleteReminder');

    Route::post('task/assign/{id}', 'TaskController@addAssignee');
    Route::delete('task/assign/{task_id}/{staff_id}', 'TaskController@deleteAssignee');
    Route::post('task/follower/{id}', 'TaskController@addFollower');
    Route::delete('task/follower/{task_id}/{staff_id}', 'TaskController@deleteFollower');

    Route::post('task/copy/{id}', 'TaskController@copyData');
    Route::get('year', 'TaskController@year');
    Route::get('task/staff/{task_id}', 'TaskController@listStaffByTask');
    Route::delete('task/tag/{id}/{tag_id}', 'TaskController@deleteTag');

    // api task-estimate
    Route::get('task/estimate/{id}', 'TaskController@getListByEstimate');

    // api task-lead
    Route::get('task/lead/{id}', 'TaskController@getListByLead');

    // api task-timer
    Route::get('task/timer/{projectid}', 'TaskTimeController@listByProject');
    Route::post('task/timer', 'TaskTimeController@store');
    Route::put('task/timer/{id}', 'TaskTimeController@update');
    Route::delete('task/timer/{id}', 'TaskTimeController@destroy');
    // Route::post('task-timer/staff/{id}', 'TaskTimeController@getListByStaff');
    Route::get('task-timer/staff/{id}', 'TaskTimeController@getListByStaff');
    Route::post('task-timer/task/{id}', 'TaskTimeController@createByTask');
    Route::get('task-timer/task/{id}', 'TaskTimeController@getListByTask');
    // Route::post('task-timer/project/{id}', 'TaskTimeController@taskTimeByProject');
    Route::get('task-timer/project/chartLog/{id}', 'TaskTimeController@chartLog');
    Route::get('task-timer/project/{id}', 'TaskTimeController@taskTimeByProject');
    Route::get('task-timer/staff/logged', 'TaskTimeController@getLoggedTime');

    // Route::get('lead-reminder', 'ReminderController@index');
    Route::post('reminder/customer/{id}', 'ReminderController@createByCustomer');
    Route::post('reminder/expense/{id}', 'ReminderController@createByExpense');
    Route::get('reminder/lead/{id}', 'ReminderController@show');
    Route::put('reminder/{id}', 'ReminderController@update');
    Route::delete('reminder/{id}', 'ReminderController@destroy');
    Route::get('reminder/customer/{id}', 'ReminderController@getListByCustomer');
    Route::get('reminder/expense/{id}', 'ReminderController@getListByExpense');
    Route::get('reminder/lead/{id}', 'ReminderController@getListByLead');
    Route::post('reminder/lead/{id}', 'ReminderController@createByLead');
    Route::post('reminder/estimate/{id}', 'ReminderController@createByEstimate');
    Route::post('reminder/task/{id}', 'ReminderController@createByTask');
    Route::get('reminder/task/{id}', 'ReminderController@getListByTask');
    Route::post('reminder/creditNote/{id}', 'ReminderController@createByCreditNote');
    Route::get('reminder/creditNote/{id}', 'ReminderController@getListByCreditNote');

    // api estimaste-reminder
    Route::get('reminder/estimate/{id}', 'ReminderController@getByEstimaste');

    // api ticket-reminder
    Route::get('reminder/ticket/{id}', 'ReminderController@getListByTicket');
    Route::post('reminder/ticket/{id}', 'ReminderController@createByTicket');

    // api ticket-reminder
    Route::get('reminder/proposal/{id}', 'ReminderController@getListByProposal');
    Route::post('reminder/proposal/{id}', 'ReminderController@createByProposal');

    //api invoice-reminder
    Route::get('reminder/invoice/{id}', 'ReminderController@getListByInvoice');
    Route::post('reminder/invoice/{id}', 'ReminderController@createByInvoice');
});
