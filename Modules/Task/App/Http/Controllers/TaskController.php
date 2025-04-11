<?php

namespace Modules\Task\App\Http\Controllers;

use App\Exports\TaskExport;
use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Modules\Task\Repositories\Task\TaskInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Task\Repositories\Reminders\ReminderInterface;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Task\Entities\Task;

class TaskController extends Controller
{
    private $taskRepository;
    private $reminderRepository;
    const errorMess = 'Task không tồn tại';
    const errorCreateMess = 'Tạo công việc thất bại';
    const errorUpdateMess = 'Cập nhật công việc thất bại';
    const errorDeleteMess = 'Xóa công việc thất bại';
    const errorChangeActive = 'Thay đổi trạng thái thất bại';
    const errorChangePriority = 'Thay đổi mức độ ưu tiên thất bại';
    const errorCreateTimer = 'Tạo timer thất bại';
    const errorDeleteTimer = 'Xóa timer thất bại';
    const errorCreateCheckList = 'Tạo checklist thất bại';
    const errorDeleteCheckList = 'Xóa checklist thất bại';
    const errorCreateComment = 'Tạo nhận xét thất bại';
    const errorDeleteComment = 'Xóa nhận xét thất bại';
    const errorCreateAssigned = 'Tạo Assigne thất bại';
    const errorDeleteAssigned = 'Xóa Assigne thất bại';
    const errorCreateFollower = 'Tạo Follower thất bại';
    const errorDeleteFollower = 'Xóa Follower thất bại';
    const errorCopyMess = 'Sao chép thất bại';
    const errorDeleteTag = "Xoá tag thất bại";

    public function __construct(TaskInterface $taskRepository, ReminderInterface $reminderRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->reminderRepository = $reminderRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/task",
     *     tags={"Task"},
     *     summary="Get all task filters",
     *     description="Retrieve all task filters based on different criteria.",
     *     operationId="getAllTasks",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, id, start_date, or due_date, recurring",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="porject_id",
     *         in="query",
     *         description="Search by project_id",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="int",
     *             default="1"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Filter tasks by tags",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Filter by today's tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Filter by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Filter by upcoming tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Filter by assigned tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Filter by tasks with followers",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Filter by not assigned tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Filter by recurring tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Filter by billable tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Filter by billed tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Filter by member tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid filter by task value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        try {
            $task = $this->taskRepository->listAll($request->all());
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/count",
     *     tags={"Task"},
     *     summary="Get the count of tasks by status and time frame",
     *     description="Retrieve the count of tasks grouped by status. Supports filtering by week, month, or no filter based on the provided 'case' parameter.",
     *     operationId="getSummaryTaskByStatus",
     *     @OA\Parameter(
     *         name="case",
     *         in="query",
     *         required=false,
     *         description="Filter tasks by time frame. Possible values: 0 (current week), 1 (current month), or leave empty for no time filter.",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             default=null
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 description="Error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 description="Error message"
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function count(Request $request)
    {
        try {
            $task = $this->taskRepository->count($request);
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    // public function getListByTask($id, Request $request)
    // {
    //     return $this->taskRepository->getListByTask($id, $request->all());
    // }

    /**
     * @OA\Post(
     *     path="/api/task",
     *     tags={"Task"},
     *     summary="Create a new task",
     *     description="Create a new task with the given details.",
     *     operationId="createTask",
     *     @OA\RequestBody(
     *         description="Payload to create a new task",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CreateTaskRequest")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CreateTaskRequest")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CreateTaskRequest")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        Log::debug($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'description' => 'bail|nullable|string',
            'priority' => 'bail|required|integer|in:1,2,3,4',
            'start_date' => 'bail|nullable|date',
        ], [
            'name.required' => 'Bạn chưa nhập tiêu đề',
            'name.max' => 'Tiêu đề không quá 191 ký tự',
            'priority.*' => 'Không hợp lệ',
            'start_date.*' => 'Ngày không hợp lệ',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $task = $this->taskRepository->create($data);
            if (!$task) {
                return Result::fail(static::errorCreateMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/{id}",
     *     tags={"Task"},
     *     summary="Get a specific task by ID",
     *     description="Retrieve the details of a specific task using its ID.",
     *     operationId="getTaskById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the task to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $task = $this->taskRepository->findId($id);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/task/{id}",
     *     tags={"Task"},
     *     summary="Update an existing task",
     *     description="Update an existing task by its ID.",
     *     operationId="updateTask",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the task to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to update an existing task",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/CreateTaskRequest")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/CreateTaskRequest")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CreateTaskRequest")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation exception"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'description' => 'bail|nullable|string',
            'priority' => 'bail|required|integer|in:1,2,3,4',
            'start_date' => 'bail|nullable|date',
            // 'rel_id' => 'bail|required|integer|exists:leads,id',
            // 'hourly_rate' => 'bail|required|numeric',
        ], [
            'name.required' => 'Bạn chưa nhập tiêu đề',
            'name.max' => 'Tiêu đề không quá 191 ký tự',
            'priority.*' => 'Không hợp lệ',
            'start_date.*' => 'Ngày không hợp lệ',
            // 'rel_id.*' => 'không hợp lệ',
            // 'hourly_rate.*' => 'không hợp lệ',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        try {
            $data = $request->all();
            $task = $this->taskRepository->update($id, $data);
            if (!$task) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/{id}",
     *     tags={"Task"},
     *     summary="Delete a task",
     *     description="Delete a specific task by its ID.",
     *     operationId="deleteTask",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the task to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $task = $this->taskRepository->destroy($id);
            if (!$task) {
                return Result::fail(static::errorDeleteMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/project/{project_id}",
     *     tags={"Task"},
     *     summary="Get a specific Project by ID",
     *     description="Retrieve details of a specific project by its ID.",
     *     operationId="getTaskByProjectId",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="ID of the project to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, id, start_date, due_date",
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
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
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
            $task = $this->taskRepository->listByProject($project_id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/staff/{project_id}/{task_id}",
     *     tags={"Task"},
     *     summary="Get a task staff",
     *     description="Retrieve details of a specific task staff by its ID.sẽ sửa tên hàm sau khi hệ thống chạy ổn định, vì đâylà hàmlấy danh sách nhân viên theo projectID và taskid ",
     *     operationId="getStaffByTaskId",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="ID of the tasks.rel_id to retrieve ",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the tasks.id to retrieve ",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StaffModel"),
     *         @OA\XmlContent(ref="#/components/schemas/StaffModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task staff ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task staff not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function listByStaff($project_id, $task_id)
    {
        try {
            $task = $this->taskRepository->listByStaff($project_id, $task_id);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * list task out of project
     *
     * @return \Illuminate\Http\Response
     */
    public function listOutProject()
    {
        return $this->taskRepository->listOutProject();
    }

    /**
     * @OA\Put(
     *     path="/api/task/status/{task_id}",
     *     tags={"Task"},
     *     summary="Change the status of an existing task",
     *     description="Update the status of a specific task by its ID.",
     *     operationId="changeTaskStatusByTaskId",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the task to update the status",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to update the status of the task",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"status"},
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer",
     *                         description="The new status of the task"
     *                     )
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"status"},
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer",
     *                         description="The new status of the task"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function changeStatus($id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->changeStatus($id, $data);
            if (!$task) {
                return Result::fail(static::errorChangeActive);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorChangeActive);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/task/priority/{task_id}",
     *     tags={"Task"},
     *     summary="Change the priority of an existing task",
     *     description="Update the priority level of a specific task by its ID.",
     *     operationId="ChangeTaskPriority",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The ID of the task to update the priority",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to update the priority of the task",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"priority"},
     *                     @OA\Property(
     *                         property="priority",
     *                         type="integer",
     *                         description="The new priority level of the task"
     *                     )
     *                 )
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(
     *                     type="object",
     *                     required={"priority"},
     *                     @OA\Property(
     *                         property="priority",
     *                         type="integer",
     *                         description="The new priority level of the task"
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function changePriority($id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->changePriority($id, $data);
            if (!$task) {
                return Result::fail(static::errorChangePriority);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorChangePriority);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/checklist/{task_id}",
     *     tags={"Task"},
     *     summary="Create a new checklist for a task",
     *     description="Add a new checklist to a specific task by its ID.",
     *     operationId="createTaskChecklist",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The ID of the task to add a checklist to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new task checklist",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskChecklistModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskChecklistModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task checklist created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskChecklistModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function addChecklist($task_id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->addChecklist($task_id, $data);
            if (!$task) {
                return Result::fail(static::errorCreateCheckList);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateCheckList);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/checklist/{task_id}",
     *     tags={"Task"},
     *     summary="Delete a task checklist",
     *     description="Delete a task checklist by its ID",
     *     operationId="deleteTaskChecklist",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The ID of the task checklist to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task checklist deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task checklist ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task checklist not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function deleteChecklist($id)
    {
        try {
            $task = $this->taskRepository->destroyChecklist($id);
            if (!$task) {
                return Result::fail(static::errorDeleteCheckList);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteCheckList);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/comment/{task_id}",
     *     tags={"Task"},
     *     summary="Create a new comment for a task",
     *     description="Add a new comment to a specific task by its ID.",
     *     operationId="createTaskComment",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The ID of the task to add a comment to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new task comment",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskCommentModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskCommentModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task comment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskCommentModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function addComment($task_id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->addComment($task_id, $data);
            if (!$task) {
                return Result::fail(static::errorCreateComment);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateComment);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/task/comment/{taskComment_id}",
     *     tags={"Task"},
     *     summary="Update an existing task comment",
     *     description="Update an existing task comment by its ID",
     *     operationId="updateTaskComment",
     *     @OA\Parameter(
     *         name="taskComment_id",
     *         in="path",
     *         description="The ID of the task comment to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to update an existing task comment",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskCommentModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskCommentModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task comment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskCommentModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskCommentModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task comment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function updateComment($comment_id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->updateComment($comment_id, $data);
            if (!$task) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/comment/{taskComment_id}",
     *     tags={"Task"},
     *     summary="Delete a task comment",
     *     description="Delete a task comment by its ID",
     *     operationId="deleteTaskComment",
     *     @OA\Parameter(
     *         name="taskComment_id",
     *         in="path",
     *         description="The ID of the task comment to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task comment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task comment ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task comment not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function deleteComment($id)
    {
        try {
            $task = $this->taskRepository->destroyComment($id);
            if (!$task) {
                return Result::fail(static::errorDeleteComment);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteComment);
        }
    }

    public function addReminder(Request $request, $task_id)
    {
        return $this->reminderRepository->create($request->all());
    }

    public function updateReminder($task_id, Request $request)
    {
        return $this->reminderRepository->update($task_id, $request->all());
    }

    public function deleteReminder(Request $request, $task_id)
    {
        return $this->reminderRepository->destroy($task_id);
    }

    /**
     * @OA\Post(
     *     path="/api/task/assign/{id}",
     *     tags={"Task"},
     *     summary="Create a new assignee for a task",
     *     description="Add a new assignee to a specific task by its ID.",
     *     operationId="createTaskAssignee",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the task to add a assignee to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new task Assignee",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskAssignModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskAssignModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task assignee created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskAssignModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function addAssignee(Request $request)
    {
        try {
            $task = $this->taskRepository->addAssignee($request->all());
            if (!$task) {
                return Result::fail(static::errorCreateAssigned);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateAssigned);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/assign/{task_id}/{staff_id}",
     *     tags={"Task"},
     *     summary="Delete a task assignee",
     *     description="Delete a task assignee by its ID",
     *     operationId="deleteTaskAssignee",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The task_id of the task assignee to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="staff_id",
     *         in="path",
     *         description="The staff_id of the task assignee to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task assignee deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task assignee ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task assignee not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function deleteAssignee($task_id, $staff_id)
    {
        try {
            $task = $this->taskRepository->destroyAssignee($task_id, $staff_id);
            if (!$task) {
                return Result::fail(static::errorDeleteAssigned);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteAssigned);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/follower/{id}",
     *     tags={"Task"},
     *     summary="Create a new follower for a task",
     *     description="Add a new follower to a specific task by its ID.",
     *     operationId="createTaskFollower",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the task to add a follower to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Payload to create a new task follower",
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(ref="#/components/schemas/TaskFollowerModel")
     *             ),
     *             @OA\MediaType(
     *                 mediaType="application/x-www-form-urlencoded",
     *                 @OA\Schema(ref="#/components/schemas/TaskFollowerModel")
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task assignee created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaskFollowerModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function addFollower(Request $request)
    {
        try {
            $task = $this->taskRepository->addFollower($request->all());
            if (!$task) {
                return Result::fail(static::errorCreateFollower);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateFollower);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/follower/{task_id}/{staff_id}",
     *     tags={"Task"},
     *     summary="Delete a task follower",
     *     description="Delete a task follower by its ID",
     *     operationId="deleteTaskFollower",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The task_id of the task follower to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="staff_id",
     *         in="path",
     *         description="The staff_id of the task follower to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task follower deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task follower ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task follower not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function deleteFollower($task_id, $staff_id)
    {
        try {
            $task = $this->taskRepository->destroyFollower($task_id, $staff_id);
            if (!$task) {
                return Result::fail(static::errorDeleteFollower);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteFollower);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/lead/{lead_id}",
     *     tags={"Task"},
     *     summary="Get a specific lead by ID",
     *     description="Retrieve details of a specific lead by its ID.",
     *     operationId="getLeadById",
     *     @OA\Parameter(
     *         name="lead_id",
     *         in="path",
     *         description="ID of the lead to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task lead not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByLead($id, Request $request)
    {
        try {
            $task = $this->taskRepository->getListByLead($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/estimate/{id}",
     *     tags={"Task"},
     *     summary="Get a specific estimate by ID",
     *     description="Retrieve details of a specific estimate by its ID.",
     *     operationId="getEstimateById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the estimate to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByEstimate($id, Request $request)
    {
        try {
            $task = $this->taskRepository->getListByEstimate($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/expense/{expense_id}",
     *     tags={"Task"},
     *     summary="Get a specific expense by ID",
     *     description="Retrieve details of a specific expense by its ID.",
     *     operationId="getExpenseById",
     *     @OA\Parameter(
     *         name="expense_id",
     *         in="path",
     *         description="ID of the expense to retrieve ",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
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
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByExpense($id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->getListByExpense($id, $data);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/contract/{contract_id}",
     *     tags={"Task"},
     *     summary="Get a specific contract by ID",
     *     description="Retrieve details of a specific contract by its ID.",
     *     operationId="getContractById",
     *     @OA\Parameter(
     *         name="contract_id",
     *         in="path",
     *         description="ID of the contract to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
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
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contract not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByContract($id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->getListByContract($id, $data);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/proposal/{proposal_id}",
     *     tags={"Task"},
     *     summary="Get a specific proposal by ID",
     *     description="Retrieve details of a specific proposal by its ID.",
     *     operationId="getProposalById",
     *     @OA\Parameter(
     *         name="proposal_id",
     *         in="path",
     *         description="ID of the proposal to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
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
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Proposal not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByProposal($id, Request $request)
    {
        try {
            $data = $request->all();
            $task = $this->taskRepository->getListByProposal($id, $data);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/invoice/{invoice_id}",
     *     tags={"Task"},
     *     summary="Get task invoice",
     *     description="Retrieve the invoice for a specific task.",
     *     operationId="getTaskProjectInvoice",
     *     @OA\Parameter(
     *         name="invoice_id",
     *         in="path",
     *         description="The ID of the invoice to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, or due_date",
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
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="The number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByInvoice($id, Request $request)
    {
        try {
            $task = $this->taskRepository->getListByInvoice($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/project/count/{project_id}",
     *     tags={"Task"},
     *     summary="Get task project count",
     *     description="Retrieve the task count for a specific project.",
     *     operationId="getTaskProjectCount",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="The ID of the project to retrieve the task count for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="task_count",
     *                 type="integer",
     *                 description="The number of tasks in the project"
     *             )
     *         ),
     *         @OA\XmlContent(
     *             type="object",
     *             @OA\Property(
     *                 property="task_count",
     *                 type="integer",
     *                 description="The number of tasks in the project"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function countByProject($id)
    {
        try {
            $task = $this->taskRepository->countByProject($id);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/project/{project_id}",
     *     tags={"Task"},
     *     summary="Get task filter project",
     *     description="Retrieve a filtered list of tasks for a specific project based on various criteria.",
     *     operationId="getTaskFilterProject",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="The ID of the project to retrieve filtered tasks for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter tasks by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             example="[2,3]",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Filter tasks that are for today",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Filter tasks by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Filter upcoming tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Filter tasks by assigned status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Filter tasks by follower status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Filter tasks by unassigned status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Filter tasks by recurring status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Filter tasks by billable status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Filter tasks by billed status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Filter tasks by member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByProject($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByProject($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/project/{project_id}/task",
     *     tags={"Task"},
     *     summary="Get task project task",
     *     description="Retrieve a filtered list of tasks for a specific project based on various criteria.",
     *     operationId="getProjectTask",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="The ID of the project to retrieve tasks for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter tasks by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             example="[2,3]",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Filter tasks that are for today",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Filter tasks by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Filter upcoming tasks",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Filter tasks by assigned status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Filter tasks by follower status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Filter tasks by unassigned status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Filter tasks by recurring status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Filter tasks by billable status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Filter tasks by billed status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Filter tasks by member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter project not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByProjectId($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByProjectId($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/copy/{task_id}",
     *     tags={"Task"},
     *     summary="Save a copy of the task",
     *     description="Save a copy of the task.",
     *     operationId="copyTask",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="The ID of the task to retrieve tasks for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assign",
     *         in="query",
     *         required=false,
     *         description="Whether to update assign members",
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Whether to update status members",
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CreateTaskRequest")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function copyData($id, Request $request)
    {
        try {
            $task = $this->taskRepository->copyData($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorCopyMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCopyMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/contract/{contract_id}",
     *     tags={"Task"},
     *     summary="Get task filter contract",
     *     description="Get a list of task filter contract.",
     *     operationId="getTaskFilterContract",
     *     @OA\Parameter(
     *         name="contract_id",
     *         in="path",
     *         description="Id of the contract to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Search by status",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example="[1,2]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter contract not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByContract($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByContract($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/year",
     *     tags={"Task"},
     *     summary="Get the year of all tasks",
     *     description="Retrieve the total year of all tasks.",
     *     operationId="getAllYear",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="year",
     *                 type="integer",
     *                 description="Total year of tasks"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid count value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function year()
    {
        try {
            $task = $this->taskRepository->year();
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/staff/{task_id}",
     *     tags={"Task"},
     *     summary="Get a specific staff by ID",
     *     description="Retrieve the details of a specific staff using its ID.",
     *     operationId="getStaffById",
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the staff to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StaffModel"),
     *         @OA\XmlContent(ref="#/components/schemas/StaffModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function listStaffByTask($task_id)
    {
        try {
            $task = $this->taskRepository->listStaffByTask($task_id);
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/lead/{lead_id}",
     *     tags={"Task"},
     *     summary="Get task filter lead",
     *     description="Get a list of task filter lead.",
     *     operationId="getTaskFilterLead",
     *     @OA\Parameter(
     *         name="lead_id",
     *         in="path",
     *         description="Id of the lead to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter lead not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByLead($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByLead($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/task/tag/{rel_id}/{tag_id}",
     *     tags={"Task"},
     *     summary="Delete a task tag",
     *     description="Delete a task tag by its ID",
     *     operationId="deleteTaskTag",
     *     @OA\Parameter(
     *         name="rel_id",
     *         in="path",
     *         description="The rel_id of the taggable to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         description="The tag_id of the task taggable to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task tag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task tag ID"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task tag not found"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function deleteTag($task_id, $tag_id)
    {
        try {
            $task = $this->taskRepository->destroyTag($task_id, $tag_id);
            if (!$task) {
                return Result::fail(static::errorDeleteTag);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteTag);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/ticket/{ticket_id}",
     *     tags={"Task"},
     *     summary="Get a specific ticket by id",
     *     description="Retrieve details of a specific ticket by its id.",
     *     operationId="getTicketById",
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="path",
     *         description="Id of the ticket to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, start_date, due_date",
     *         required=false,
     *         @OA\Schema(
     *             default="",
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByTicket($id, Request $request)
    {
        try {
            $task = $this->taskRepository->getListByTicket($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/ticket/{ticket_id}",
     *     tags={"Task"},
     *     summary="Get task filter ticket",
     *     description="Get a list of task filter tickets.",
     *     operationId="getTaskFilterTicket",
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="path",
     *         description="Id of the ticket to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Search by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example="[2,3]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter ticket not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByTicket($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByTicket($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/customer/{customer_id}",
     *     tags={"Task"},
     *     summary="Get task filter customer",
     *     description="Get a list of task filter customer.",
     *     operationId="getTaskFilterCustomer",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="ID of the customer to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search by task name",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Search by task ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Search by start date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-02-20"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Search by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="integer"),
     *             example="[2,3]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter customer not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByCustomer($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByCustomer($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/expense/{expense_id}",
     *     tags={"Task"},
     *     summary="Get task filter expense",
     *     description="Get a list of task filter expense.",
     *     operationId="getTaskFilterExpense",
     *     @OA\Parameter(
     *         name="expense_id",
     *         in="path",
     *         description="ID of the expense to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Search by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example="[2,3]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter expense not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByExpense($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByExpense($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/invoice/{invoice_id}",
     *     tags={"Task"},
     *     summary="Get task by invoice",
     *     description="Get a list of tasks filtered by invoice.",
     *     operationId="getTaskByInvoice",
     *     @OA\Parameter(
     *         name="invoice_id",
     *         in="path",
     *         description="ID of the invoice to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter tasks by status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="string",
     *             example="[2,3]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Filter tasks by today's date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Filter tasks by due date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Filter tasks by upcoming date",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Filter tasks by assigned member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Filter tasks by follower",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Filter tasks by not assigned",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Filter tasks by recurring status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Filter tasks by billable status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Filter tasks by billed status",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Filter tasks by member",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="",
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter by invoice not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByInvoice($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByInvoice($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/filter/proposal/{proposal_id}",
     *     tags={"Task"},
     *     summary="Get task filter proposal",
     *     description="Get a list of task filter proposals.",
     *     operationId="getTaskFilterProposal",
     *     @OA\Parameter(
     *         name="proposal_id",
     *         in="path",
     *         description="ID of the proposal to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Search by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             default="",
     *             example="[2,3]"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="today",
     *         in="query",
     *         description="Search by today",
     *         required=false,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dueDate",
     *         in="query",
     *         description="Search by due date",
     *         required=false,
     *         @OA\Schema(
     *             type="date",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="upcoming",
     *         in="query",
     *         description="Search by upcoming",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="assigned",
     *         in="query",
     *         description="Search by assigned",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="follower",
     *         in="query",
     *         description="Search by follower",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="notAssigned",
     *         in="query",
     *         description="Search by not assigned",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="recurring",
     *         in="query",
     *         description="Search by recurring",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billable",
     *         in="query",
     *         description="Search by billable",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="billed",
     *         in="query",
     *         description="Search by billed",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="member",
     *         in="query",
     *         description="Search by member",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
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
     *         @OA\JsonContent(ref="#/components/schemas/TaskModel"),
     *         @OA\XmlContent(ref="#/components/schemas/TaskModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Filter proposal not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid id supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function filterTaskByProposal($id, Request $request)
    {
        try {
            $task = $this->taskRepository->filterTaskByProposal($id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/task/bulkAction",
     *     summary="Cập nhật nhiều task cùng lúc",
     *     description="Cập nhật các thuộc tính của nhiều task dựa trên các tham số truyền vào.",
     *     tags={"Task"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="id", type="array", @OA\Items(type="integer"), description="Danh sách các task_id cần cập nhật"),
     *                 @OA\Property(property="isProject", type="boolean", description="Cập nhật thông tin dự án (true: có, false: không)"),
     *                 @OA\Property(property="project_id", type="integer", description="ID dự án nếu có"),
     *                 @OA\Property(property="isStatus", type="boolean", description="Cập nhật trạng thái (true: có, false: không)"),
     *                 @OA\Property(property="status", type="integer", description="Trạng thái của task (1: mới, 2: đang làm, 3: hoàn thành...)"),
     *                 @OA\Property(property="isStartDate", type="boolean", description="Cập nhật ngày bắt đầu (true: có, false: không)"),
     *                 @OA\Property(property="startDate", type="string", format="date", description="Ngày bắt đầu của task"),
     *                 @OA\Property(property="isDueDate", type="boolean", description="Cập nhật ngày kết thúc (true: có, false: không)"),
     *                 @OA\Property(property="dueDate", type="string", format="date", description="Ngày kết thúc của task"),
     *                 @OA\Property(property="isPriority", type="boolean", description="Cập nhật độ ưu tiên (true: có, false: không)"),
     *                 @OA\Property(property="priority", type="integer", description="Độ ưu tiên của task"),
     *                 @OA\Property(property="isTag", type="boolean", description="Cập nhật nhãn (true: có, false: không)"),
     *                 @OA\Property(property="tag", type="array", @OA\Items(type="object", @OA\Property(property="name", type="string"), @OA\Property(property="tag_order", type="integer")), description="Danh sách nhãn của task"),
     *                 @OA\Property(property="isAssigned", type="boolean", description="Cập nhật người được giao (true: có, false: không)"),
     *                 @OA\Property(property="assigned", type="array", @OA\Items(type="object", @OA\Property(property="staff_id", type="integer")), description="Danh sách người được giao task"),
     *                 @OA\Property(property="isFollower", type="boolean", description="Cập nhật người theo dõi (true: có, false: không)"),
     *                 @OA\Property(property="follower", type="array", @OA\Items(type="object", @OA\Property(property="staff_id", type="integer")), description="Danh sách người theo dõi task")
     *             )
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật task thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống. Vui lòng thử lại sau.")
     *         )
     *     )
     * )
     */
    public function bulkAction(Request $request)
    {
        try {
            $task = $this->taskRepository->bulkAction($request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/milestones/{milestones_id}",
     *     tags={"Task"},
     *     summary="Get all task milestones",
     *     description="Retrieve details of a specific task milestones by its ID.",
     *     operationId="getAllTaskMilestones",
     *     @OA\Parameter(
     *         name="milestones_id",
     *         in="path",
     *         required=true,
     *         description="ID of the task for which to retrieve milestones",
     *         @OA\Schema(type="integer"),
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of records per page",
     *         @OA\Schema(
     *             type="integer",
     *             default=0
     *         ),
     *         example=10
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid task milestones value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function listTaskByMilestones($project_id, Request $request)
    {
        try {
            $task = $this->taskRepository->listTaskByMilestones($project_id, $request->all());
            if (!$task) {
                return Result::fail(static::errorMess);
            }
            return Result::success($task);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/task/current-week",
     *     summary="Get tasks in the current week",
     *     description="Retrieve tasks created or updated during the current week along with their statuses in English.",
     *     tags={"Task"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Task 1"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="status_text", type="string", example="Not Started"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20 10:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-21 15:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid request.")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function getTasksInCurrentWeek()
    {
        $data = $this->taskRepository->getTasksInCurrentWeek();
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/task/has-tasks-by-status",
     *     summary="Check if tasks exist by status",
     *     description="Check if there are tasks grouped by their status for a specific time frame (week or month).",
     *     tags={"Task"},
     *     @OA\Parameter(
     *         name="timeFrame",
     *         in="query",
     *         required=true,
     *         description="Time frame for filtering tasks. 1 = current week, 2 = current month.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="total", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid timeFrame parameter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid timeFrame. Use 1 for week or 2 for month.")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function countTasksByStatus(Request $request)
    {
        $data = $this->taskRepository->countTasksByStatus($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/task-summary-by-project",
     *     summary="Get task summary by project",
     *     description="Retrieve a summary of tasks grouped by project.",
     *     tags={"Task"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="projects",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="project_id", type="integer", example=1),
     *                     @OA\Property(property="total_tasks", type="integer", example=15)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function taskSummaryByProject(Request $request)
    {
        $data = $this->taskRepository->taskSummaryByProject($request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/task-summary-by-customer",
     *     summary="Get task summary by customer",
     *     description="Retrieve a summary of tasks grouped by customer.",
     *     tags={"Task"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="customers",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="customer_id", type="integer", example=2),
     *                     @OA\Property(property="total_tasks", type="integer", example=20)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function taskSummaryByCustomer(Request $request)
    {
        $data = $this->taskRepository->taskSummaryByCustomer($request->all());
        return Result::success($data);
    }
}
