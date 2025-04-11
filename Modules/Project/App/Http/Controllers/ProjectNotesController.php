<?php

namespace Modules\Project\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Modules\Project\Repositories\ProjectNotes\ProjectNotesInterface;

class ProjectNotesController extends Controller
{
    protected $projectNotesRepository;
    
    const messageCreateError = 'Tạo ghi chú thất bại';
    const messageCreateSuccess = 'Tạo ghi chú thành công';
    const messageCodeError = 'ghi chú không tồn tại';
    const messageDeleteError = 'Xóa ghi chú thất bại';
    const messageUpdateError = 'Sửa ghi chú thất bại';

    public function __construct(ProjectNotesInterface $projectNotesRepository)
    {
        $this->projectNotesRepository = $projectNotesRepository;
    }

    public function index(Request $request)
    {
        return $this->projectNotesRepository->listAll($request->all());
    }
    /**
     * Store a newly created resource in storage.
     * @param  int  $project_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [

        ], [


        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        return $this->projectNotesRepository->create($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->projectNotesRepository->destroy($id);
    }

    /**
     * @OA\Get(
     *     path="/api/project/notes/{project_id}",
     *     tags={"Project"},
     *     summary="Get a specific project by ID",
     *     description="Retrieve details of a specific project by its ID.",
     *     operationId="getProjectNoteById",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="project_id of the table project_notes to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *         @OA\JsonContent(ref="#/components/schemas/ProjectNote"),
     *         @OA\XmlContent(ref="#/components/schemas/ProjectNote")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project note not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByProject($id, Request $request)
    {
        try {
            $data = $request->all();
            $projectNote = $this->projectNotesRepository->getListByProject($id, $data);
            if (!$projectNote) {
                return Result::fail(static::messageCodeError);
            }
            return Result::success($projectNote);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::messageCodeError);
        }
    }

    public function update($id, Request $request)
    {
        return $this->projectNotesRepository->update($id, $request->all());
    }

}
