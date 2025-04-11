<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Campaign\CampaignContentInterface;

class CampaignContentController extends Controller
{
    const errorMess = 'Campaign content không tồn tại';
    const errorCreateMess = 'Tạo campaign content thất bại';
    const errorUpdateMess = 'Chỉnh sửa campaign content thất bại';
    const successDeleteMess = 'Xoá campaign content thành công';
    const errorDeleteMess = 'Xoá campaign content thất bại';
    protected $campaignContentRepository;

    public function __construct(CampaignContentInterface $campaignContentRepository)
    {
        $this->campaignContentRepository = $campaignContentRepository;
    }

    public function index(Request $request)
    {
        $queyData = $request->all();
        $data = $this->campaignContentRepository->listAll($queyData);
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->campaignContentRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errorCreateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->campaignContentRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->campaignContentRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->campaignContentRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
