<?php

namespace Modules\Task\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Task\Repositories\TaskType\TaskTypeInterface;

class TaskTypeController extends Controller
{
    private $taskTypeRepo;

    public function __construct(TaskTypeInterface $taskTypeRepo)
    {
        $this->taskTypeRepo = $taskTypeRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->taskTypeRepo->listAll($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'description' => 'bail|nullable|string|max:300',
        ], [
            'name.required'=>'Bạn chưa nhập tên',
            'name.max'=>'Tên không quá 300 ký tự',
            'description.*'=>'Mô tả không quá 300 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        return $this->taskTypeRepo->create($request->all());
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|max:191',
            'description' => 'bail|nullable|string|max:300',
        ], [
            'name.required'=>'Bạn chưa nhập tên',
            'name.max'=>'Tên không quá 300 ký tự',
            'description.*'=>'Mô tả không quá 300 ký tự',
        ]);
        if ($validator->fails()) {
            return Result::requestInvalid($validator->errors());
        }
        return $this->taskTypeRepo->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->taskTypeRepo->destroy($id);
    }

    public function listSelect()
    {
        return $this->taskTypeRepo->listSelect();
    }
}
