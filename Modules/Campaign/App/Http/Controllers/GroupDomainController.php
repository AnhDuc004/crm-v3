<?php

namespace Modules\Campaign\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Campaign\Repositories\Domain\GroupDomainInterface;

class GroupDomainController extends Controller
{
    const errorMess = 'Miền không tồn tại';
    const errorCreateMess = 'Tạo miền thất bại';
    const errorUpdateMess = 'Chỉnh sửa miền thất bại';
    const successDeleteMess = 'Xoá miền thành công';
    const errorDeleteMess = 'Xoá miền thất bại';

    protected $groupDomainRepository;

    public function __construct(GroupDomainInterface $groupDomainRepository)
    {
        $this->groupDomainRepository = $groupDomainRepository;
    }

    public function index(Request $request)
    {
        $data = $this->groupDomainRepository->listAll($request->all());
        return Result::success($data);
    }

    public function store(Request $request)
    {
        try {
            $data = $this->groupDomainRepository->create($request->all());
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
        $data = $this->groupDomainRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->groupDomainRepository->update($id, $request->all());
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
        $data = $this->groupDomainRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }
}
