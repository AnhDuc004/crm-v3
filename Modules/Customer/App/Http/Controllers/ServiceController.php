<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Service\ServiceInterface;

class ServiceController extends Controller
{
    const messageError = 'Dịch vụ không tồn tại';
    const messageCreateErr = 'Tạo dịch vụ thất bại';
    const messageUpdateErr = 'Chỉnh sửa dịch vụ thất bại';
    const messageDeleteErr = 'Xoá dịch vụ thất bại';

    protected $serviceRepository;

    public function __construct(ServiceInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->serviceRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        try {
            $service = $this->serviceRepository->create($request->all());
            if (!$service) {
                return Result::fail(self::messageError);
            }
            return Result::success($service);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateErr);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $service = $this->serviceRepository->update($id, $request->all());
            if (!$service) {
                return Result::fail(self::messageUpdateErr);
            }
            return Result::success($service);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDeleteErr);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->serviceRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::messageDeleteErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDeleteErr);
        }
    }
}
