<?php

namespace Modules\Subscription\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Subscription\Repositories\Subscription\SubscriptionInterface;

class SubscriptionController extends Controller
{
    const errorMess = 'Đăng ký không tồn tại';
    const errorCreateMess = "Thêm mới đăng ký thất bại";
    const errorUpdateMess = "Cập nhật đăng ký thất bại";
    const errorDeleteMess = "Xóa đăng ký thất bại";
    const errCustomerMess = "Khách hàng không tồn tại";
    protected $subscriptionRepository;

    public function __construct(SubscriptionInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(Request $request)
    {
        return Result::success($this->subscriptionRepository->listAll($request->all()));
    }

    public function show($id)
    {
        $data = $this->subscriptionRepository->findId($id);
        if (!$data) {
            return Result::fail(self::errorMess);
        }
        return Result::success($data);
    }

    public function getListByCustomer($id, Request $request)
    {
        return Result::success($this->subscriptionRepository->getListByCustomer($id, $request->all()));
    }

    public function createByCustomer(Request $request, $id)
    {
        try {
            $data = $this->subscriptionRepository->createByCustomer($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function store(Request $request, $id)
    {
        try {
            $data = $this->subscriptionRepository->create($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $data = $this->subscriptionRepository->update($id, $request->all());
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
        $data = $this->subscriptionRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function countNotSubscribed()
    {
        return Result::success($this->subscriptionRepository->countNotSubscribed());
    }

    public function countActive()
    {
        return Result::success($this->subscriptionRepository->countActive());
    }

    public function countFuture()
    {
        return Result::success($this->subscriptionRepository->countFuture());
    }

    public function countPastDue()
    {
        return Result::success($this->subscriptionRepository->countPastDue());
    }

    public function countPaid()
    {
        return Result::success($this->subscriptionRepository->countPaid());
    }

    public function countIncomplete()
    {
        return Result::success($this->subscriptionRepository->countIncomplete());
    }

    public function countCanceled()
    {
        return Result::success($this->subscriptionRepository->countCanceled());
    }

    public function countIncompleteExpired()
    {
        return Result::success($this->subscriptionRepository->countIncompleteExpired());
    }

    public function getByProject($id, Request $request)
    {
        return Result::success($this->subscriptionRepository->getByProject($id, $request->all()));
    }
}
