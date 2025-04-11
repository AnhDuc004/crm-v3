<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Campaign\CampaignGroupInterface;

class CampaignGroupController extends Controller
{
    const errorMess = 'Campaign group không tồn tại';
    const errorCreateMess = 'Tạo campaign group thất bại';
    const errorUpdateMess = 'Chỉnh sửa campaign group thất bại';
    const successDeleteMess = 'Xoá campaign group thành công';
    const errorDeleteMess = 'Xoá campaign group thất bại';
    protected $campaignGroupRepository;

    public function __construct(CampaignGroupInterface $campaignGroupRepository)
    {
        $this->campaignGroupRepository = $campaignGroupRepository;
    }

    public function index(Request $request)
    {
        $data = $this->campaignGroupRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->campaignGroupRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->campaignGroupRepository->findId($id);
        if (!$data) {
            return Result::fail(static::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->campaignGroupRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->campaignGroupRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorDeleteMess);
        }
    }
}
