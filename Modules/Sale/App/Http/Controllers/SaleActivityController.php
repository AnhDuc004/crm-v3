<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Helpers\Result;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sale\Repositories\SaleActivity\SaleActivityInterface;

class SaleActivityController extends Controller
{
    const messageError = 'Nhật ký không tồn tại';
    const errDelete = 'Xóa nhật ký thất bại';

    protected $saleActivityRepository;

    public function __construct(SaleActivityInterface $saleActivityRepository)
    {
        $this->saleActivityRepository = $saleActivityRepository;
    }

    public function getSaleActivityByEstimate($id, Request $request)
    {
        return Result::success($this->saleActivityRepository->getSaleActivityByEstimate($id, $request->all()));
    }

    public function getSaleActivityByInvoice($id, Request $request)
    {
        return Result::success($this->saleActivityRepository->getSaleActivityByInvoice($id, $request->all()));
    }

    public function destroy($id)
    {
        $data = $this->saleActivityRepository->destroy($id);
        if (!$data) {
            return Result::fail(self::errDelete);
        }
        return Result::success($data);
    }
}
