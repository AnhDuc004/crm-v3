<?php

namespace Modules\Sale\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Result;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Sale\Entities\PaymentMode;
use Modules\Sale\Repositories\PaymentModes\PaymentModesInterface;

class PaymentModesController extends Controller
{
    protected $paymentModesRepository;
    const errorMess = 'Ngân hàng không tồn tại';
    const errorCreateMess = "Thêm mới ngân hàng thất bại";
    const errorUpdateMess = "Cập nhật ngân hàng thất bại";
    const errorDeleteMess = "Xóa ngân hàng thất bại";

    public function __construct(PaymentModesInterface $paymentModesRepository)
    {
        $this->paymentModesRepository = $paymentModesRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/paymentModes/{id}",
     *     summary="Lấy thông tin chi tiết của một phương thức thanh toán",
     *     description="Trả về thông tin chi tiết của một phương thức thanh toán dựa trên ID",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của phương thức thanh toán cần tìm",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin chi tiết của phương thức thanh toán",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModeModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy phương thức thanh toán",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy phương thức thanh toán")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi máy chủ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        $paymentMode = $this->paymentModesRepository->findId($id);
        return Result::success($paymentMode);
    }

    /**
     * @OA\Get(
     *     path="/api/paymentModes",
     *     summary="Get All Payment Modes",
     *     description="Lấy danh sách tất cả các phương thức thanh toán",
     *     operationId="getAllPaymentModes",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang (nếu có phân trang)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Số lượng bản ghi mỗi trang (nếu có phân trang)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách phương thức thanh toán",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully fetched payment modes"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PaymentModeModel")),
     *             @OA\Property(property="pagination", type="object", 
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total_records", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid request")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        return Result::success($this->paymentModesRepository->listAll($request));
    }

    /**
     * @OA\Post(
     *     path="/api/paymentModes",
     *     summary="Tạo mới một phương thức thanh toán",
     *     description="Thêm một phương thức thanh toán mới vào hệ thống",
     *     tags={"Sale"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModeModel")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo phương thức thanh toán thành công",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModeModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu đầu vào không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Đã xảy ra lỗi máy chủ")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function store(Request $request)
    {
        try {
            $paymentMode = $this->paymentModesRepository->create($request->all());
            if (!$paymentMode) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($paymentMode);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/paymentModes/{id}",
     *     summary="Update Payment Mode",
     *     description="Cập nhật thông tin phương thức thanh toán",
     *     operationId="updatePaymentMode",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của phương thức thanh toán",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cần cập nhật cho phương thức thanh toán",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PaymentModeModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật phương thức thanh toán thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment mode updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/PaymentModeModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid data provided")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Phương thức thanh toán không tìm thấy",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment mode not found or failed to update")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);
        $paymentMode = $this->paymentModesRepository->update($id, $data);

        if (!$paymentMode) {
            return Result::fail(self::errorMess);
        }
        return Result::success($paymentMode);
    }

    /**
     * @OA\Delete(
     *     path="/api/paymentModes/{id}",
     *     summary="Delete a Payment Mode",
     *     description="Xóa một phương thức thanh toán theo ID",
     *     operationId="deletePaymentMode",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của phương thức thanh toán cần xóa",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa phương thức thanh toán thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully deleted payment mode"),
     *             @OA\Property(property="data", type="object", description="Thông tin phương thức thanh toán đã xóa")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không thể xóa phương thức thanh toán",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete payment mode")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy phương thức thanh toán với ID đã cho",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment mode not found")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        $payment = $this->paymentModesRepository->destroy($id);
        if (!$payment) {
            return Result::fail(self::errorDeleteMess);
        }
        return Result::success($payment);
    }

    /**
     * @OA\Put(
     *     path="/api/payment-modes/{id}/toggle-active",
     *     tags={"Sale"},
     *     summary="Thay đổi trạng thái hoạt động của Payment Mode",
     *     description="Thay đổi trạng thái hoạt động của Payment Mode theo ID.",
     *     operationId="toggleActiveByPaymentMode",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của PaymentMode",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentModeModel"),
     *         @OA\XmlContent(ref="#/components/schemas/PaymentModeModel")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid payment mode ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment mode ID not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function toggleActive($id)
    {
        try {
            $paymentMode = $this->paymentModesRepository->toggleActive($id);
            return Result::success($paymentMode);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(static::errorUpdateMess);
        }
    }
}
