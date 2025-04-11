<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Campaign\CampaignInterface;

class CampaignController extends Controller
{
    const errorMess = 'Campaign không tồn tại';
    const errorCreateMess = 'Tạo campaign thất bại';
    const errorUpdateMess = 'Chỉnh sửa campaign thất bại';
    const successDeleteMess = 'Xoá campaign thành công';
    const errorDeleteMess = 'Xoá campaign thất bại';
    protected $campaignRepository;

    public function __construct(CampaignInterface $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function index(Request $request)
    {
        $data = $this->campaignRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->campaignRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->campaignRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->campaignRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->campaignRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
