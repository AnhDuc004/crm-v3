<?php

namespace Modules\KnowledgeBase\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\KnowledgeBase\Repositories\KnowledgeBaseGroup\KnowledgeBaseGroupInterface;

class KnowledgeBaseGroupController extends Controller
{
    const errorMessage = 'Nhóm không tồn tại';
    const errorCreateMessage = 'Tạo nhóm thất bại';
    const errorUpdateMessage = 'Chỉnh sửa nhóm thất bại';
    const errorDeleteMessage = 'Xoá nhóm thất bại';
    protected $knowledgeBaseGroupRepository;

    public function __construct(KnowledgeBaseGroupInterface $knowledgeBaseGroupRepository)
    {
        $this->knowledgeBaseGroupRepository = $knowledgeBaseGroupRepository;
    }

    public function index(Request $request)
    {
        $data = $this->knowledgeBaseGroupRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->knowledgeBaseGroupRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errorCreateMessage);
        }
    }

    public function show($id)
    {
        $data = $this->knowledgeBaseGroupRepository->findId($id);
        if (!$data) {
            Result::fail(self::errorMessage);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->knowledgeBaseGroupRepository->update($id, $request->all());
            if (!$data) {
                Result::fail(self::errorUpdateMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errorUpdateMessage);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->knowledgeBaseGroupRepository->destroy($id);
            if (!$data) {
                Result::fail(self::errorDeleteMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errorDeleteMessage);
        }
    }
}
