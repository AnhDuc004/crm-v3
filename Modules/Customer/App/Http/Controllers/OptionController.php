<?php

namespace Modules\Customer\App\Http\Controllers;

use App\Helpers\Result;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Option\OptionInterface;

class OptionController extends Controller
{
    const messageCodeError = 'Option không tồn tại';
    const messageCreateErr = 'Tạo Option thất bại';
    const messageUpdateErr = 'Chỉnh sửa Option thất bại';
    const messageDeleteErr = 'Xóa Option thất bại';

    protected $optionRepository;

    public function __construct(OptionInterface $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->optionRepository->listAll($request->all()));
    }

    public function store(Request $request)
    {
        try {
            $data = $this->optionRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::messageCreateErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateErr);
        }
    }

    public function show($id)
    {
        $data = $this->optionRepository->findId($id);
        if (!$data) {
            return Result::fail(self::messageCodeError);
        }
        return Result::success($data);
    }

    public function update(Request $request)
    {
        try {
            $data = $this->optionRepository->update($request->all());
            if (!$data) {
                return Result::fail(self::messageUpdateErr);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateErr);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->optionRepository->destroy($id);
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
