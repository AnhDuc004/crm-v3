<?php

namespace Modules\Task\Repositories\TaskTime;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Helpers\Result;
use App\Traits\ActivityKey;
use App\Traits\LogActivityTrait;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Tag;
use Modules\Task\Entities\TaskTimer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Project\Entities\ProjectActivity;
use Modules\Task\Entities\Task;


class TaskTimeRepository implements TaskTimeInterface
{
    use LogActivityTrait;
    const messageCodeError = 'Thời gian công việc không tồn tại';
    const messageCreateError = 'Tạo thời gian công việc thất bại';
    const messageUpdateError = 'Cập nhật thời gian công việc thất bại';
    const messageDeleteError = 'Xóa thời gian công việc thất bại';

    public function listByProject($project_id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $search = isset($request["search"]) ? $request["search"] : null;
        $staff = isset($request["staff"]) ? json_decode($request["staff"]) : null;

        $baseQuery = TaskTimer::leftJoin('tasks', 'task_timers.task_id', '=', 'tasks.id')

            ->leftJoin('projects', 'projects.id', '=', 'tasks.rel_id')
            ->leftJoin('staff', 'staff.id', '=', 'task_timers.staff_id')
            ->leftjoin('taggables', 'taggables.rel_id', '=', 'task_timers.id')
            ->leftJoin('tags', 'tags.id', '=', 'taggables.tag_id')
            ->where('projects.id', $project_id);
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('staff.first_name', 'like', '%' . $search . '%')
                        ->orWhere('staff.last_name', 'like', '%' . $search . '%')
                        ->orWhere('tasks.name', 'like', '%' . $search . '%')
                        ->orWhere('tags.name', 'like', '%' . $search . '%');
                }
            );
        }

        if ($staff) {
            $baseQuery = $baseQuery->whereIn('staff.id', $staff);
        }

        $baseQuery->with('tags', 'staff:id,first_name,last_name,profile_image', 'task:id,name,rel_id,rel_type')->select('task_timers.*')
            ->selectRaw("FROM_UNIXTIME(task_timers.start_time, '%Y-%m-%d') as start_date")
            ->selectRaw("FROM_UNIXTIME(task_timers.end_time, '%Y-%m-%d') as end_date")
            ->selectRaw("round((task_timers.end_time - task_timers.start_time ) / 3600) as time")
            ->selectRaw("round(((task_timers.end_time - task_timers.start_time ) / 3600), 2) as timeDecimal")->distinct();
        if ($limit > 0) {
            $task_timer = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $task_timer = $baseQuery->get();
        }
        return $task_timer;
    }

    public function create($request)
    {
        $task_timer = new TaskTimer($request);
        $task_timer->created_by = Auth::id();
        $task_timer->save();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $task_timer->tags()->attach($tag['id'], ['rel_type' => 'timesheet', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $task_timer->tags()->attach($tg->id, ['rel_type' => 'timesheet', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        $data = TaskTimer::where('id', $task_timer->id)
            ->with(
                'tags',
                'staff:id,first_name,last_name,profile_image',
                'task:id,name,rel_id,rel_type'
            )
            ->first();
        return $data;
    }
    public function update($id, $request)
    {
        $task_timer = TaskTimer::find($id);
        $task_timer->fill($request);
        $task_timer->taggable()->delete();
        if (isset($request['tags'])) {
            foreach ($request['tags'] as $key => $tag) {
                if (isset($tag['id'])) {
                    $task_timer->tags()->attach($tag['id'], ['rel_type' => 'timesheet', 'tag_order' => $tag['tag_order']]);
                } else {
                    $tg = Tag::where('name', $tag['name'])->first();
                    if ($tg === null) {
                        $tg = new Tag($tag);
                        $tg->save();
                    }
                    $task_timer->tags()->attach($tg->id, ['rel_type' => 'timesheet', 'tag_order' => $tag['tag_order']]);
                }
            }
        }
        $task = Task::find($task_timer->task_id);
        if ($task && $task->rel_type == 'project') {
            $this->createProjectActivity($task->rel_id, ActivityKey::UPDATE_TIMESHEETS_BY_TASK_IN_PROJECT);
        }
        $task_timer->save();
        $task_timer->updated_by = Auth::id();
        $data = TaskTimer::where('id', $task_timer->id)->with('tags', 'staff:id,first_name,last_name,profile_image', 'task:id,name,rel_id,rel_type')->first();
        return $data;
    }

    public function destroy($id)
    {
        $task_timer = TaskTimer::find($id);
        $task = Task::find($task_timer->task_id);
        if ($task && $task->rel_type == 'project') {
            $this->createProjectActivity($task->rel_id, ActivityKey::DELETE_TIMESHEETS_BY_TASK_IN_PROJECT);
        }
        $result = $task_timer->delete();
        return $result;
    }

    public function getListByStaff($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $time_sheet = isset($request["time_sheet"]) ? $request["time_sheet"] : null;
        //convert startdate và enddate sang timestamp
        $start_date = isset($request["start_date"]) ? $request["start_date"] : null;
        $end_date = isset($request["end_date"]) ? $request["end_date"] : null;
        // $startdate = Carbon::parse($startdate)->timestamp;
        // $enddate = Carbon::parse($enddate)->timestamp;
        // Lấy ngày,tháng,năm trong tháng hiện tại
        $monthStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $thisMonth = [$monthStartDate, $monthEndDate];
        // Lấy ngày,tháng,năm trong tháng trước
        $lastMonthStartDate = Carbon::now()->addMonths(-1)->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->addMonths(-1)->endOfMonth()->format('Y-m-d');
        $lastMonth = [$lastMonthStartDate, $lastMonthEndDate];
        // lấy ngày,tháng,năm trong tuần
        $weekStartDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $weekEndDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $thisWeek = [$weekStartDate, $weekEndDate];
        // lấy ngày,tháng,năm tuần trướ
        $lastWeekStartDate = Carbon::today()->subDays(7)->startOfWeek()->format('Y-m-d');
        $lastWeekEndDate = Carbon::today()->subDays(7)->endOfWeek()->format('Y-m-d');
        $lastWeek = [$lastWeekStartDate, $lastWeekEndDate];
        //convert start_timer và end_timer về dạng date
        $baseQuery = TaskTimer::where('staff_id', $id)->select('task_timers.*');
        if ($time_sheet == 1) {
            $baseQuery = $baseQuery->whereBetween(DB::raw("FROM_UNIXTIME(start_time, '%Y-%m-%d')"), $thisMonth)->selectRaw("FROM_UNIXTIME(start_time, '%Y-%m-%d') as start_date")
                ->selectRaw("FROM_UNIXTIME(end_time, '%Y-%m-%d') as end_date")->selectRaw("round((end_time - start_time ) / 3600) as time")
                ->selectRaw("round(((end_time - start_time ) / 3600), 2) as timeDecimal")->distinct();
        } else if ($time_sheet == 2) {
            $baseQuery = $baseQuery->whereBetween(DB::raw("FROM_UNIXTIME(start_time, '%Y-%m-%d')"), $lastMonth)->selectRaw("FROM_UNIXTIME(start_time, '%Y-%m-%d') as start_date")
                ->selectRaw("FROM_UNIXTIME(end_time, '%Y-%m-%d') as end_date")->selectRaw("round((end_time - start_time ) / 3600) as time")
                ->selectRaw("round(((end_time - start_time ) / 3600), 2) as timeDecimal")->distinct();
        } else if ($time_sheet == 3) {
            $baseQuery = $baseQuery->whereBetween(DB::raw("FROM_UNIXTIME(start_time, '%Y-%m-%d')"), $thisWeek)->selectRaw("FROM_UNIXTIME(start_time, '%Y-%m-%d') as start_date")
                ->selectRaw("FROM_UNIXTIME(end_time, '%Y-%m-%d') as end_date")->selectRaw("round((end_time - start_time ) / 3600) as time")
                ->selectRaw("round(((end_time - start_time ) / 3600), 2) as timeDecimal")->distinct();
        } else {
            $baseQuery = $baseQuery->whereBetween(DB::raw("FROM_UNIXTIME(start_time, '%Y-%m-%d')"), $lastWeek)->selectRaw("FROM_UNIXTIME(start_time, '%Y-%m-%d') as start_date")
                ->selectRaw("FROM_UNIXTIME(end_time, '%Y-%m-%d') as end_date")->selectRaw("round((end_time - start_time ) / 3600) as time")
                ->selectRaw("round(((end_time - start_time ) / 3600), 2) as timeDecimal")->distinct();
        }
        if ($start_date) {
            $baseQuery = $baseQuery->where('start_time', $start_date)
                ->where('end_time', $end_date)
                ->selectRaw("FROM_UNIXTIME(start_time, '%Y-%m-%d') as start_date")
                ->selectRaw("FROM_UNIXTIME(end_time, '%Y-%m-%d') as end_date")->selectRaw("round((end_time - start_time ) / 3600) as time")
                ->selectRaw("round(((end_time - start_time ) / 3600), 2) as timeDecimal")->distinct();
        }
        $baseQuery = $baseQuery->with('task:id,name,rel_id,rel_type')->orderBy('created_at', 'desc');
        //get dữ liệu đã convert
        if ($limit > 0) {
            $task_timer = $baseQuery->paginate($limit);
        } else {
            $task_timer = $baseQuery->get();
        }
        return $task_timer;
    }

    public function createByTask($id, $request)
    {
        DB::beginTransaction();
        try {
            $task_timer = new TaskTimer($request);
            $task_timer->created_by = Auth::user()->id;
            $task_timer->task_id = $id;
            $task = Task::find($id);
            if ($task && $task->rel_type == 'project') {
                $this->createProjectActivity($task->rel_id, ActivityKey::CREATE_TIMESHEETS_BY_TASK_IN_PROJECT);
            }
            $task_timer->save();
            if (isset($request['tags'])) {
                foreach ($request['tags'] as $key => $tag) {
                    if (isset($tag['id'])) {
                        $task_timer->tags()->attach($tag->id, ['rel_type' => $tag['rel_type'], 'tag_order' => $tag['tag_order']]);
                    } else {
                        $tg = Tag::where('name', $tag['name'])->first();
                        if ($tg === null) {
                            $tg = new Tag($tag);
                            $tg->save();
                        }
                        $task_timer->tags()->attach($tg->id, ['rel_type' => 'time_sheet', 'tag_order' => $tag['tag_order']]);
                    }
                }
            }
            DB::commit();
            return Result::success($task_timer);
        } catch (Exception $ex) {
            DB::rollback();
            Log::error($ex->getMessage());
            return Result::fail(static::messageCreateError);
        }
    }

    public function getListByTask($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = TaskTimer::where('task_id', $id)
            ->leftJoin('tasks', 'task_timers.task_id', '=', 'tasks.id')
            ->leftJoin('staff', 'staff.id', '=', 'task_timers.staff_id');

        $baseQuery->with('staff:id,first_name,last_name,profile_image', 'task:id,name,rel_id,rel_type')->select('task_timers.*')
            ->selectRaw("FROM_UNIXTIME(task_timers.start_time, '%Y-%m-%d') as start_date")
            ->selectRaw("FROM_UNIXTIME(task_timers.end_time, '%Y-%m-%d') as end_date")
            ->selectRaw("((task_timers.end_time - task_timers.start_time)/3600) as time")->distinct();
        if ($limit > 0) {
            $task_timer = $baseQuery->paginate($limit);
        } else {
            $task_timer = $baseQuery->get();
        }
        return Result::success($task_timer);
    }

    public function taskTimeByProject($id, $request)
    {
        $date = Carbon::now()->toDateString();
        $taskTimer = TaskTimer::leftJoin('tasks', 'task_timers.task_id', '=', 'tasks.id')
            ->where('tasks.rel_id', $id)
            ->where('tasks.rel_type', 'project')
            ->select('task_timers.*')
            ->selectRaw("FROM_UNIXTIME(task_timers.start_time, '%Y-%m-%d') as start_date")
            ->selectRaw("FROM_UNIXTIME(task_timers.end_time, '%Y-%m-%d') as end_date")
            ->selectRaw("round((task_timers.end_time - task_timers.start_time) / 3600) as time")
            ->selectRaw("round(((task_timers.end_time - task_timers.start_time) / 3600), 2) as timeDecimal")
            ->whereDate('task_timers.start_time', '=', $date)
            ->distinct()
            ->get();
        return $taskTimer;
    }

    public function getLoggedTime()
    {
        $date = Carbon::now()->format('Y-m-d');
        // Lấy ngày,tháng,năm trong tuần hiện tại
        $weekStartDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $weekEndDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        $weekDate = [$weekStartDate, $weekEndDate];
        // Lấy ngày,tháng,năm trong tuần trước
        $lastWeekStartDate = Carbon::today()->subDays(7)->startOfWeek()->format('Y-m-d');
        $lastWeekEndDate = Carbon::today()->subDays(7)->endOfWeek()->format('Y-m-d');
        $lastWeekDate = [$lastWeekStartDate, $lastWeekEndDate];
        // Lấy ngày,tháng,năm trong tháng hiện tại
        $monthStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $monthDate = [$monthStartDate, $monthEndDate];
        // Lấy ngày,tháng,năm trong tháng trước
        $lastMonthStartDate = Carbon::now()->addMonths(-1)->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->addMonths(-1)->endOfMonth()->format('Y-m-d');
        $lastMonthDate = [$lastMonthStartDate, $lastMonthEndDate];
        $today = 0;
        $week = 0;
        $lastWeek = 0;
        $month = 0;
        $lastMonth = 0;
        $loggedToday = TaskTimer::where('staffid', Auth::user()->staffid)->whereRaw("date(created_at)", $date)
            ->orWhereRaw("date(updated_at)", $date)->get();
        foreach ($loggedToday as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $today += (($endTime - $startTime) / 3600);
        }
        $loggedWeek = TaskTimer::where('staffid', Auth::user()->staffid)->whereBetween(DB::raw('date(created_at)'), $weekDate)
            ->orWhereBetween(DB::raw('date(updated_at)'), $weekDate)->get();
        foreach ($loggedWeek as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $week += (($endTime - $startTime) / 3600);
        }

        $loggedLastWeek = TaskTimer::where('staffid', Auth::user()->staffid)->whereBetween(DB::raw('date(created_at)'), $lastWeekDate)
            ->orWhereBetween(DB::raw('date(updated_at)'), $lastWeekDate)->get();
        foreach ($loggedLastWeek as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $lastWeek += (($endTime - $startTime) / 3600);
        }

        $loggedMonth = TaskTimer::where('staffid', Auth::user()->staffid)->whereBetween(DB::raw('date(created_at)'), $monthDate)
            ->orWhereBetween(DB::raw('date(updated_at)'), $monthDate)->get();
        foreach ($loggedMonth as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $month += (($endTime - $startTime) / 3600);
        }

        $loggedLastMonth = TaskTimer::where('staffid', '=', Auth::user()->staffid)->whereBetween(DB::raw('date(created_at)'), $lastMonthDate)
            ->orWhereBetween(DB::raw('date(updated_at)'), $lastMonthDate)->get();
        foreach ($loggedLastMonth as $value) {
            $startTime = $value->start_time;
            $endTime = $value->end_time;
            $lastMonth += (($endTime - $startTime) / 3600);
        }
        return [
            'TotalLoggedTime' => round($today, 2),
            'LastMonthLoggedTime' => round($lastMonth, 2),
            'ThisMonthLoggedTime' => round($month, 2),
            'LastWeekLoggedTime' => round($lastWeek, 2),
            'ThisWeekLoggedTime' => round($week, 2)
        ];
    }

    public function chartLog($id)
    {
        // Lấy thời gian của các ngày trong tuần
        $daysOfWeek = [
            "Monday"    => Carbon::now()->startOfWeek()->format('Y-m-d'),
            "Tuesday"   => Carbon::now()->startOfWeek()->addDays(1)->format('Y-m-d'),
            "Wednesday" => Carbon::now()->startOfWeek()->addDays(2)->format('Y-m-d'),
            "Thursday"  => Carbon::now()->startOfWeek()->addDays(3)->format('Y-m-d'),
            "Friday"    => Carbon::now()->startOfWeek()->addDays(4)->format('Y-m-d'),
            "Saturday"  => Carbon::now()->startOfWeek()->addDays(5)->format('Y-m-d'),
            "Sunday"    => Carbon::now()->endOfWeek()->format('Y-m-d'),
        ];

        // Hàm lấy thời gian đăng nhập cho một ngày cụ thể
        $getLoggedTimeForDay = function ($day) use ($id) {
            return TaskTimer::leftJoin('tasks', 'tasks.id', '=', 'task_timers.task_id')
                ->where('tasks.rel_id', $id)
                ->whereRaw("DATE(FROM_UNIXTIME(task_timers.start_time)) = ?", [$day])
                ->selectRaw('ROUND(SUM(CASE 
                                    WHEN task_timers.end_time > task_timers.start_time 
                                    THEN (task_timers.end_time - task_timers.start_time) / 3600 
                                    ELSE 0 END), 2) as total_hours')
                ->pluck('total_hours')
                ->first() ?? 0;
        };

        // Lấy thời gian đăng nhập cho từng ngày trong tuần
        $loggedTime = [];
        foreach ($daysOfWeek as $dayName => $dayDate) {
            $loggedTime["logged" . $dayName] = $getLoggedTimeForDay($dayDate);
        }

        // Lấy tổng thời gian đăng nhập trong tuần
        $weekLog = TaskTimer::leftJoin('tasks', 'tasks.id', '=', 'task_timers.task_id')
            ->where('tasks.rel_id', $id)
            ->whereBetween(DB::raw("DATE(FROM_UNIXTIME(task_timers.start_time))"), [$daysOfWeek['Monday'], $daysOfWeek['Sunday']])
            ->selectRaw('ROUND(SUM(CASE 
                                WHEN task_timers.end_time > task_timers.start_time 
                                THEN (task_timers.end_time - task_timers.start_time) / 3600 
                                ELSE 0 END), 2) as total_hours')
            ->pluck('total_hours')
            ->first() ?? 0;

        $loggedTime['loggedWeek'] = $weekLog;

        return [
            'daysOfWeek' => $daysOfWeek,
            'loggedTime' => $loggedTime,
        ];
    }
}
