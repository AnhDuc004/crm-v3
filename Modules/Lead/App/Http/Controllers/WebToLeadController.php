<?php

namespace Modules\Lead\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Lead\Repositories\WebToLead\WebToLeadInterface;

class WebToLeadController extends Controller
{
    const errMessage = 'Khách từ web không tồn tại';
    const errCreateMessage = 'Thêm mới khách từ web thất bại';
    const errUpdateMessage = 'Cập nhật khách từ web thất bại';
    const errDeleteMessage = 'Xóa khách từ web thất bại';

    protected $WebToLeadRepository;

    public function __construct(WebToLeadInterface $WebToLeadRepository)
    {
        $this->WebToLeadRepository = $WebToLeadRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->WebToLeadRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->WebToLeadRepository->create($request->all());
            if (!$data) {
                Result::fail(self::errCreateMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errCreateMessage);
        }
    }

    public function show($id)
    {
        $data = $this->WebToLeadRepository->findId($id);
        if (!$data) {
            Result::fail(self::errMessage);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->WebToLeadRepository->update($id, $request->all());
            if (!$data) {
                Result::fail(self::errUpdateMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errUpdateMessage);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->WebToLeadRepository->destroy($id);
            if (!$data) {
                Result::fail(self::errDeleteMessage);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Result::fail(self::errDeleteMessage);
        }
    }
}
