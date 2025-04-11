<?php

namespace Modules\Task\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Task\Repositories\TaskTime\TaskTimeInterface;
use Illuminate\Support\Facades\Log;

class TaskTimeController extends Controller
{
    protected $taskTimeRepository;
    const messageCodeError = 'Thời gian công việc không tồn tại';
    const messageCreateError = 'Tạo thời gian công việc thất bại';
    const messageUpdateError = 'Cập nhật thời gian công việc thất bại';
    const messageDeleteError = 'Xóa thời gian công việc thất bại';

    public function __construct(TaskTimeInterface $taskTimeRepository)
    {
        $this->taskTimeRepository = $taskTimeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/task/timer/{projectid}",
     *     tags={"Task"},
     *     summary="Get a specific task timer by ID",
     *     description="Retrieve the details of a specific task timer using its ID.",
     *     operationId="getTaskTimerById",
     *     @OA\Parameter(
     *         name="projectid",
     *         in="path",
     *         description="ID of the task timer to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by staff.first_name, staff.last_name, tasks.name, tags.name",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskTimerModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task timer not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function listByProject($project_id, Request $request)
    {
        try {
            $tasktimer = $this->taskTimeRepository->listByProject($project_id, $request->all());
            if (!$tasktimer) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($tasktimer);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/timer",
     *     tags={"Task"},
     *     summary="Add a new taskTimer ",
     *     description="Add a new taskTimer ",
     *     operationId="addTaskTimer",
     *     @OA\RequestBody(
     *         description="Create a new taskTimer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskTimerModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskTimerModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskTimerModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'bail|required|string|max:64',
            'end_time' => 'bail|required|string|max:64',
            'task_id' => 'bail|required|integer',
            'staff_id' => 'bail|required|integer',
            'note' => 'bail|nullable|string|max:500',
        ], [
            'start_time.*' => 'Bạn chưa nhập thời gian bắt đầu',
            'end_time.*' => 'Bạn chưa nhập thời gian kết thúc',
            'task_id.required' => 'Bạn chưa nhập công việc',
            'staff_id.required' => 'Bạn chưa nhập thành viên',
            'note.*' => 'Ghi chú không quá 500 ký tự',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $taskTime = $this->taskTimeRepository->create($data);
            if (!$taskTime) {
                return Result::fail(static::messageCreateError);
            }
            return Result::success($taskTime);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCreateError);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/task/timer/{id}",
     *     tags={"Task"},
     *     summary="Update an existing taskTimer",
     *     description="Update an existing taskTimer by ID",
     *     operationId="updateTaskTimer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the taskTimer to update",
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         description="Update an existing taskTimer",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskTimerModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskTimerModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskTimerModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskTimerModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskTimer not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'bail|required|string|max:64',
            'end_time' => 'bail|required|string|max:64',
            'task_id' => 'bail|required|integer',
            'staff_id' => 'bail|required|integer',
            'note' => 'bail|nullable|string|max:500',
        ], [
            'start_time.*' => 'Bạn chưa nhập thời gian bắt đầu',
            'end_time.*' => 'Bạn chưa nhập thời gian kết thúc',
            'task_id.required' => 'Bạn chưa nhập công việc',
            'staff_id.required' => 'Bạn chưa nhập thành viên',
            'note.*' => 'Ghi chú không quá 500 ký tự',

        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $taskTime = $this->taskTimeRepository->update($id, $data);
            if (!$taskTime) {
                return Result::fail(static::messageUpdateError);
            }
            return Result::success($taskTime);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageUpdateError);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/timer/{id}",
     *     tags={"Task"},
     *     summary="Delete a taskTimer",
     *     description="Delete a taskTimer by ID",
     *     operationId="deleteTaskTimer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of taskTimer to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid taskTimer ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="TaskTimer not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $taskTime = $this->taskTimeRepository->destroy($id);
            if (!$taskTime) {
                return Result::fail(static::messageDeleteError);
            }
            return Result::success($taskTime);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageDeleteError);
        }
    }

    public function getListByStaff($id, Request $request)
    {
        try {
            $taskTime = $this->taskTimeRepository->getListByStaff($id, $request->all());
            if (!$taskTime) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($taskTime);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    public function createByTask($id, Request $request)
    {
        return $this->taskTimeRepository->createByTask($id, $request->all());
    }

    public function getListByTask($id, Request $request)
    {
        return $this->taskTimeRepository->getListByTask($id, $request->all());
    }

    public function taskTimeByProject($id, Request $request)
    {
        try {
            $taskTime = $this->taskTimeRepository->taskTimeByProject($id, $request);
            return Result::success($taskTime);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    public function getLoggedTime()
    {
        return $this->taskTimeRepository->getLoggedTime();
    }

    /**
     * @OA\Get(
     *     path="/api/task-timer/project/chartLog/{id}",
     *     tags={"Task"},
     *     summary="Hàm lấy ghi chú worklog cho tuần hiện tại theo project id",
     *     description="Hàm cho ra lịch tuần này theo thứ-ngày-tháng và tính tổng worklog đã khai báo. Hàm sẽ được thay đổi đường dẫn URI cho chính xác tác vụ sau.",
     *     operationId="getWorklogThisWeek",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id của dự án cần truy vấn",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),    
     *     @OA\Response(
     *         response="200",
     *         description="Trả về danh sách Thứ ngày theo tuần và danh sách worklog",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="day",
     *                 type="object",
     *                 @OA\Property(property="Monday", type="string", format="date", example="2024-12-02"),
     *                 @OA\Property(property="Tuesday", type="string", format="date", example="2024-12-03"),
     *                 @OA\Property(property="Wednesday", type="string", format="date", example="2024-12-04"),
     *                 @OA\Property(property="Thursday", type="string", format="date", example="2024-12-05"),
     *                 @OA\Property(property="Friday", type="string", format="date", example="2024-12-06"),
     *                 @OA\Property(property="Saturday", type="string", format="date", example="2024-12-07"),
     *                 @OA\Property(property="Sunday", type="string", format="date", example="2024-12-08")
     *             ),
     *             @OA\Property(
     *                 property="loggedTime",
     *                 type="object",
     *                 @OA\Property(property="loggedMonday", type="integer", example=0),
     *                 @OA\Property(property="loggedTuesday", type="integer", example=0),
     *                 @OA\Property(property="loggedWednesday", type="integer", example=0),
     *                 @OA\Property(property="loggedThursday", type="integer", example=0),
     *                 @OA\Property(property="loggedFriday", type="integer", example=0),
     *                 @OA\Property(property="loggedSaturday", type="integer", example=0),
     *                 @OA\Property(property="loggedWeek", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function chartLog($id)
    {
        $data = $this->taskTimeRepository->chartLog($id);
        return Result::success($data);
    }
}
