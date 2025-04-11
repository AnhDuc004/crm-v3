<?php

namespace Modules\Sale\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Sale\Repositories\Invoice\InvoiceInterface;
use Illuminate\Support\Facades\Log;
use App\Helpers\Result;

class InvoiceController extends Controller
{
    protected $invoiceRepository;
    const errorMess = 'Hóa đơn không tồn tại';
    const errorCreateMess = "Thêm mới hóa đơn thất bại";
    const errorUpdateMess = "Cập nhật hóa đơn thất bại";
    const errorDeleteMess = "Xóa hóa đơn thất bại";
    const errorPaymentMess = "Không thể thanh toán hóa đơn";
    const errCustomerMess = "Khách hàng không tồn tại";
    const errorCopyMess = "Sao chép dữ liệu thất bại";
    const errConvertMess = "Chuyển đổi thất bại";

    public function __construct(InvoiceInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/invoice",
     *     tags={"Sale"},
     *     summary="Get all invoices",
     *     description="Retrieve a list of all invoices.",
     *     operationId="getAllInvoice",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by total, total_tax, date and duedate",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             default=""
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
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceModel")
     *         ),
     *         @OA\XmlContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid invoice value"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function index(Request $request)
    {
        $invoice = $this->invoiceRepository->listAll($request->all());
        return Result::success($invoice);
    }

    /**
     * @OA\Get(
     *     path="/api/invoice/customer/{id}",
     *     tags={"Sale"},
     *     summary="Lấy danh sách hóa đơn của khách hàng",
     *     description="Truy xuất danh sách các hóa đơn liên quan đến ID khách hàng cụ thể.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Số lượng bản ghi trên một trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Số trang",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Từ khóa tìm kiếm trong ngày hóa đơn hoặc ngày đáo hạn",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách hóa đơn của khách hàng được lấy thành công",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Khách hàng không tìm thấy"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ"
     *     ),
     *     security={{"bearer": {}}}
     * )
     */
    public function createByCustomer(Request $request, $id)
    {
        $data = $this->invoiceRepository->createByCustomer($id, $request->all());
        return Result::success($data);
    }

    public function getListByCustomer($id, Request $request)
    {
        $data = $this->invoiceRepository->getListByCustomer($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Put(
     *     path="/api/invoice/{id}",
     *     tags={"Sale"},
     *     summary="Cập nhật hóa đơn",
     *     description="Cập nhật hóa đơn theo ID",
     *     operationId="updateInvoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/InvoiceModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Error updating invoice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'sale_agent' => 'required|integer',
        ]);
        try {
            $data = $this->invoiceRepository->update($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorUpdateMess);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorUpdateMess);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/invoice/{id}",
     *     summary="Xóa hóa đơn",
     *     description="Xóa hóa đơn theo ID",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hóa đơn cần xóa",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa hóa đơn thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Không thể xóa hóa đơn, không tìm thấy hoặc có lỗi xảy ra",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Không thể xóa hóa đơn"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống khi xóa hóa đơn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Lỗi hệ thống, không thể xóa hóa đơn"
     *             )
     *         )
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function destroy($id)
    {
        try {
            $data = $this->invoiceRepository->destroy($id);
            if (!$data) {
                return Result::fail(self::errorDeleteMess);
            }
            return Result::success($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorDeleteMess);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/invoice/project/{project_id}",
     *     tags={"Sale"},
     *     summary="Get a specific invoice project by ID",
     *     description="Retrieve details of a specific invoice project by its ID.",
     *     operationId="getInvoiceProjectById",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="project_id of the invoice  to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by date, duedate, company , name",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *             example="",
     *             default=""
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
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceModel"),
     *         @OA\XmlContent(ref="#/components/schemas/InvoiceModel")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByProject($id, Request $request)
    {
        $invoice = $this->invoiceRepository->getListByProject($id, $request->all());
        return Result::success($invoice);
    }

    public function getListByYearProject($id, Request $request)
    {
        $data = $this->invoiceRepository->getListByYearProject($id, $request->all());
        return Result::success($data);
    }

    /**
     * @OA\Get(
     *      path="/api/invoice/customer/year/{id}",
     *     summary="Lấy danh sách hóa đơn theo năm và khách hàng",
     *     description="API trả về danh sách hóa đơn của một khách hàng theo năm, hỗ trợ phân trang và tìm kiếm.",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của khách hàng",
     *         @OA\Schema(type="integer", example=4)
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         required=false,
     *         description="Năm cần lọc (mặc định là năm hiện tại)",
     *         @OA\Schema(type="integer", example=2025)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Số lượng hóa đơn trên mỗi trang (mặc định là 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Số trang muốn lấy (mặc định là 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công, trả về danh sách hóa đơn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="customer_id", type="integer", example=4),
     *                         @OA\Property(property="date", type="string", format="date", example="2025-01-15"),
     *                         @OA\Property(property="due_date", type="string", format="date", example="2025-02-15"),
     *                         @OA\Property(property="description", type="string", example="Payment for services")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy dữ liệu",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     ),
     *     security={{"bearer":{}}}
     * )
     */
    public function getListByYearCustomer($id, Request $request)
    {
        $invoice = $this->invoiceRepository->getListByYearCustomer($id, $request->all());

        return Result::success($invoice);
    }

    public function countInvoiceByProject($id)
    {
        return Result::success($this->invoiceRepository->countInvoiceByProject($id));
    }

    /**
     * @OA\Post(
     *     path="/api/invoice",
     *     summary="Tạo mới hóa đơn",
     *     description="Tạo mới hóa đơn",
     *     operationId="createInvoice",
     *     tags={"Sale"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceModel")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/InvoiceModel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *    security={{"bearer":{}}}
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'sale_agent' => 'required|integer',
        ]);
        try {
            $data = $this->invoiceRepository->create($request->all());
            if (!$data) {
                return Result::fail(self::errorCreateMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCreateMess);
        }
    }

    public function getListRecuringInvoice(Request $request)
    {
        return Result::success($this->invoiceRepository->getListRecuringInvoice($request->all()));
    }

    /**
     * @OA\Get(
     *     path="/api/invoice/{id}",
     *     summary="Lấy thông tin hóa đơn theo ID",
     *     description="Lấy thông tin chi tiết của hóa đơn dựa trên ID",
     *     tags={"Sale"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của hóa đơn cần lấy",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thông tin hóa đơn thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "id": 1,
     *                     "customer_id": 123,
     *                     "invoice_date": "2025-01-21",
     *                     "amount": 500,
     *                     "status": "paid"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy hóa đơn với ID đã cho",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Hóa đơn không tồn tại"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống khi lấy thông tin hóa đơn",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="fail"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Lỗi hệ thống khi lấy thông tin hóa đơn"
     *             )
     *         )
     *     ),
     *   security={{"bearer":{}}}
     * )
     */
    public function show($id)
    {
        try {
            $invoice = $this->invoiceRepository->findId($id);
            if (!$invoice) {
                return Result::fail(self::errorMess);
            }
            return Result::success($invoice);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorMess);
        }
    }

    public function payment($id, Request $request)
    {
        try {
            $data = $this->invoiceRepository->payment($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorPaymentMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorPaymentMess);
        }
    }

    public function invoiceWithCustomers($id, Request $request)
    {
        return Result::success($this->invoiceRepository->invoiceWithCustomers($id, $request->all()));
    }

    public function convertEstimateToInvoice($id, Request $request)
    {
        try {
            $data = $this->invoiceRepository->convertEstimateToInvoice($id, $request->all());
            if (!$data) {
                return Result::fail(self::errConvertMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errConvertMess);
        }
    }

    public function convertProposalToInvoice($id, Request $request)
    {
        try {
            $data = $this->invoiceRepository->convertProposalToInvoice($id, $request->all());
            if (!$data) {
                return Result::fail(self::errConvertMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errConvertMess);
        }
    }

    public function filterByInvoice(Request $request)
    {
        return Result::success($this->invoiceRepository->filterByInvoice($request->all()));
    }

    public function copyData($id, Request $request)
    {
        try {
            $data = $this->invoiceRepository->copyData($id, $request->all());
            if (!$data) {
                return Result::fail(self::errorCopyMess);
            }
            return Result::success($data);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Result::fail(self::errorCopyMess);
        }
    }

    public function filterInvoiceByProject($id, Request $request)
    {
        return Result::success($this->invoiceRepository->filterInvoiceByProject($id, $request->all()));
    }

    public function countInvoiceByCustomer($id)
    {
        return Result::success($this->invoiceRepository->countInvoiceByCustomer($id));
    }

    public function getListByYear(Request $request)
    {
        return Result::success($this->invoiceRepository->getListByYear($request->all()));
    }
}
