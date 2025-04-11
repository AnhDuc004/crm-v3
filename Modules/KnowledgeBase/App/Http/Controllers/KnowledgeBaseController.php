<?php

namespace Modules\KnowledgeBase\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\KnowledgeBase\Repositories\KnowledgeBase\KnowledgeBaseInterface;

class KnowledgeBaseController extends Controller
{
    const errorMess = 'Kiến thức cơ bản không tồn tại';
    const errorCreate = 'Tạo kiến thức cơ bản thất bại';
    const errorUpdate = 'Chỉnh sửa kiến thức cơ bản thất bại';
    const errorDelete = 'Xoá kiến thức cơ bản thất bại';
    protected $knowledgeBaseRepository;

    public function __construct(KnowledgeBaseInterface $knowledgeBaseRepository)
    {
        $this->knowledgeBaseRepository = $knowledgeBaseRepository;
    }

    public function index(Request $request)
    {
        $data = $this->knowledgeBaseRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->knowledgeBaseRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreate);
        }
    }

    public function show($id)
    {
        $data = $this->knowledgeBaseRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->knowledgeBaseRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdate);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdate);
        }
    }

    public function destroy($id)
    {
        $data = $this->knowledgeBaseRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDelete);
        }
        return Result::success($data);
    }
}
