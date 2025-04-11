<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sale\Repositories\Payment\PaymentInterface;
use Illuminate\Support\Facades\Log;
use App\Helpers\Result;
use Exception;

class PaymentController extends Controller
{
    protected $invoicePaymentRepository;
    const errorMess = 'Hóa đơn thanh toán không tồn tại';
    const errorCreateMess = "Thêm mới thanh toán thất bại";
    const errorUpdateMess = "Cập nhật thanh toán thất bại";
    const errorDeleteMess = "Xóa thanh toán thất bại";
    const errCustomerMess = "Khách hàng không tồn tại";

    public function __construct(PaymentInterface $invoicePaymentRepository)
    {
        $this->invoicePaymentRepository = $invoicePaymentRepository;
    }

    public function index(Request $request)
    {
        $data = $this->invoicePaymentRepository->listAll($request->all());
        return Result::success($data);;
    }

    /**
     * @OA\Post(
     *     path="/api/payment/customer/{id}",
     *     tags={"Sale"},
     *     summary="Tạo thanh toán cho khách hàng",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModel")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Thanh toán được tạo thành công"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function createByCustomer($id, Request $request)
    {
        try {
            $data = $this->invoicePaymentRepository->createByCustomer($id, $request->all());
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorCreateMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payment/customer/{customer_id}",
     *     tags={"Sale"},
     *     summary="Get a specific payment by ID",
     *     description="Retrieve details of a specific payment by its ID.",
     *     operationId="getPaymentById",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="invoice_id of the table payment to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by payment.date,amount, payment_modes.name,customers.company",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModel"),
     *         @OA\XmlContent(ref="#/components/schemas/PaymentModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment customer not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByCustomer($id, Request $request)
    {
        try {
            $data = $request->all();
            $payment = $this->invoicePaymentRepository->getListByCustomer($id, $data);
            if (!$payment) {
                return Result::fail(static::errorMess);
            }
            return Result::success($payment);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/payment/{id}",
     *     tags={"Sale"},
     *     summary="Cập nhật thanh toán",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        try {
            $data = $this->invoicePaymentRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(static::errorUpdateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }

    public function destroy($id)
    {
        $data = $this->invoicePaymentRepository->destroy($id);
        if (!$data) {
            return Result::fail(static::errorDeleteMess);
        }
        return Result::success($data);
    }

    public function show($id)
    {
        $data = $this->invoicePaymentRepository->findId($id);
        if (!$data) {
            return Result::fail(static::errorMess);
        }
        return Result::success($data);
    }
}
