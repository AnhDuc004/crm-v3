<?php

namespace Modules\Customer\App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Customer\Repositories\Customer\CustomerAdminInterface;

class CustomerAdminController extends Controller
{
    protected $customerRepository;

    const messageCodeError = 'Khách hàng không tồn tại';
    const errCreateMess = 'THêm mới thất bại';
    const errUpdateMess = 'Cập nhật thất bại';
    const errDeleteMess = 'Xóa thất bại';

    public function __construct(CustomerAdminInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index($id, Request $request)
    {
        $data = $this->customerRepository->findCustomer($id, $request->all());
        return Result::success($data);
    }

    public function store(Request $request, $id)
    {
        try {
            $data =  $this->customerRepository->create($id, $request->all());
            if (!$data) {
                return Result::fail(self::errCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errCreateMess);
        }
    }

    public function show($id)
    {
        $data = $this->customerRepository->findId($id);;
        if (!$data) {
            return Result::fail(self::messageCodeError);
        }
        return Result::success($data);
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $this->customerRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errUpdateMess);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errUpdateMess);
        }
    }

    public function destroy(Request $request, $id)
    {
        $data = $this->customerRepository->destroy($id, $request->all());
        if (!$data) {
            return Result::fail(self::errDeleteMess);
        }
        return Result::success($data);
    }
}
