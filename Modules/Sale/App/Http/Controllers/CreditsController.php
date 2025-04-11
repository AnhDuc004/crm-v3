<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Sale\Repositories\Credits\CreditsInterface;

class CreditsController extends Controller
{
    const messageCodeError = 'Ghi chú tín dụng không tồn tại';
    const messageCreateError = 'Tạo ghi chú tín dụng thất bại';
    const messageUpdateError = 'Sửa ghi chú tín dụng thất bại';
    const messageDeleteError = 'Xóa ghi chú tín dụng thất bại';
    const messageDeleteSucces = 'Xóa ghi chú tín dụng thành công';
    protected $creditsRepository;

    public function __construct(CreditsInterface $creditsRepository)
    {
        $this->creditsRepository = $creditsRepository;
    }

    public function getListByCreditNote($id, Request $request)
    {
        return Result::success($this->creditsRepository->getListByCreditNote($id, $request->all()));
    }

    public function createByCreditNote($id, Request $request)
    {
        try {
            $data = $this->creditsRepository->createByCreditNote($id, $request->all());
            if (!$data) {
                return Result::fail(self::messageCreateError);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageCreateError);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $credit = $this->creditsRepository->update($id, $request->all());
            if (!$credit) {
                return Result::fail(self::messageUpdateError);
            }
            return Result::success($credit);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageUpdateError);
        }
    }

    public function destroy($id)
    {
        try {
            $credit = $this->creditsRepository->destroy($id);
            if (!$credit) {
                return Result::fail(self::messageDeleteError);
            }
            return Result::success(self::messageDeleteSucces);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::messageDeleteError);
        }
    }
}
