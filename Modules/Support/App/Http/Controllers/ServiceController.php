<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Support\Repositories\Service\ServiceInterface;

class ServiceController extends Controller
{
    const errorMess = 'Dịch vụ không tồn tại';
    const errorCreateMess = 'Tạo dịch vụ thất bại';
    const errorUpdateMess = 'Chỉnh sửa dịch vụ thất bại';
    const successDeleteMess = 'Xoá dịch vụ thành công';
    const errorDeleteMess = 'Xoá dịch vụ thất bại';
    protected $serviceRepository;

    public function __construct(ServiceInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function index(Request $request)
    {
        $data = $this->serviceRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $service = $this->serviceRepository->create($request->all());
            if (!$service) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($service);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $service = $this->serviceRepository->findId($id);
        if (!$service) {
            return Result::fail(self::errorMess);
        }
        return Result::success($service);
    }

    public function update($id, Request $request)
    {
        try {
            $service = $this->serviceRepository->update($id, $request->all());
            if (!$service) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($service);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->serviceRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
