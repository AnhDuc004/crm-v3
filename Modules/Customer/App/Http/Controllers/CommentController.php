<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Comment\CommentInterface;

class CommentController extends Controller
{
    const errorMess = 'Bình luận không tồn tại';
    const errorCreateMess = 'Tạo bình luận thất bại';
    const errorUpdateMess = 'Chỉnh sửa bình luận thất bại';
    const successDeleteMess = 'Xoá bình luận thành công';
    const errorDeleteMess = 'Xoá bình luận thất bại';
    protected $commentRepository;

    public function __construct(CommentInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function index(Request $request)
    {
        $data = $this->commentRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->commentRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->commentRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try{
            $data = $this->commentRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        }catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->commentRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
