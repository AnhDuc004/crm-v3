<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Support\Repositories\TicketsStatus\TicketsStatusInterface;

class TicketsStatusController extends Controller
{
    const errorMess = 'Vé không tồn tại';
    const errorCreateMess = 'Tạo vé thất bại';
    const errorUpdateMess = 'Chỉnh sửa vé thất bại';
    const successDeleteMess = 'Xoá vé thành công';
    const errorDeleteMess = 'Xoá vé thất bại';
    protected $ticketsStatusRepository;
    public function __construct(TicketsStatusInterface $ticketsStatusRepository)
    {
        $this->ticketsStatusRepository = $ticketsStatusRepository;
    }

    public function index(Request $request)
    {
        $data = $this->ticketsStatusRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->ticketsStatusRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->ticketsStatusRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->ticketsStatusRepository->update($id, $request->all());
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
        $data = $this->ticketsStatusRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
