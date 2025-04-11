<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Campaign\CampaignImageInterface;

class CampaignImageController extends Controller
{
    const errorMess = 'Campaign image không tồn tại';
    const errorCreateMess = 'Tạo campaign image thất bại';
    const errorUpdateMess = 'Chỉnh sửa campaign image thất bại';
    const successDeleteMess = 'Xoá campaign image thành công';
    const errorDeleteMess = 'Xoá campaign image thất bại';
    protected $campaignImageRepository;

    public function __construct(CampaignImageInterface $campaignImageRepository)
    {
        $this->campaignImageRepository = $campaignImageRepository;
    }

    public function index(Request $request)
    {
        $data = $this->campaignImageRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->campaignImageRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
        return Result::success($data);
    }

    public function show($id)
    {
        $data = $this->campaignImageRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->campaignImageRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
        return Result::success($data);
    }

    public function destroy($id)
    {
        $data = $this->campaignImageRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
