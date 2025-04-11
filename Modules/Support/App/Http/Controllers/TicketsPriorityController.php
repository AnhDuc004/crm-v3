<?php

namespace Modules\Support\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Support\Repositories\TicketsPriority\TicketsPriorityInterface;

class TicketsPriorityController extends Controller
{
    const errorMess = 'Vé không tồn tại';
    const errorCreateMess = 'Tạo vé thất bại';
    const errorUpdateMess = 'Chỉnh sửa vé thất bại';
    const successDeleteMess = 'Xoá vé thành công';
    const errorDeleteMess = 'Xoá vé thất bại';
    protected $ticketsPriorityRepository;

    public function __construct(TicketsPriorityInterface $ticketsPriorityRepository)
    {
        $this->ticketsPriorityRepository = $ticketsPriorityRepository;
    }

    public function index(Request $request)
    {
        $data = $this->ticketsPriorityRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->ticketsPriorityRepository->create($request->all());
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
        $data = $this->ticketsPriorityRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->ticketsPriorityRepository->update($id, $request->all());
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
        $data = $this->ticketsPriorityRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
