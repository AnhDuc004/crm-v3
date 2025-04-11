<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Campaign\CampaignExeInterface;

class CampaignExeController extends Controller
{
    const errorMess = 'Campaign exe không tồn tại';
    const errorCreateMess = 'Tạo campaign exe thất bại';
    const errorUpdateMess = 'Chỉnh sửa campaign exe thất bại';
    const successDeleteMess = 'Xoá campaign exe thành công';
    const errorDeleteMess = 'Xoá campaign exe thất bại';
    protected $campaignExeRepository;

    public function __construct(CampaignExeInterface $campaignExeRepository)
    {
        $this->campaignExeRepository = $campaignExeRepository;
    }

    public function index(Request $request)
    {
        $data = $this->campaignExeRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->campaignExeRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errorCreateMess);
        }
        return Result::success($data);
    }

    public function show($id)
    {
        $data = $this->campaignExeRepository->findId($id);
        if (!$data) {
            Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->campaignExeRepository->update($id, $request->all());
            if (!$data) {
                Result::fail(self::errorUpdateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errorUpdateMess);
        }
        return Result::success($data);
    }

    public function destroy($id)
    {
        $data = $this->campaignExeRepository->destroy($id);
        if (!$data) {
            Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
