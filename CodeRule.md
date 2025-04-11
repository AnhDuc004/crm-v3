Sử dụng AI

Viết Model Laravel cho 
-- 10. Bảng tik_skus: Lưu trữ thông tin về SKU của sản phẩm
CREATE TABLE tik_skus (
    id BIGINT PRIMARY KEY COMMENT 'ID SKU, khóa chính, định danh duy nhất của SKU',
    sku_id BIGINT COMMENT 'ID SKU (dành cho hệ thống quản lý SKU)',
    product_id BIGINT COMMENT 'ID sản phẩm liên kết với bảng tik_products',
    seller_sku VARCHAR(255) COMMENT 'SKU của người bán',
    price JSON COMMENT 'Giá sản phẩm dưới dạng JSON (Danh sách các mức giá, giá gốc, giá sau thuế...)',
    stock_infos JSON COMMENT 'Thông tin kho dưới dạng JSON (Số lượng tồn kho, trạng thái kho...)',
    created_by INT COMMENT 'ID người tạo bản ghi',
    updated_by INT COMMENT 'ID người cập nhật bản ghi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo bản ghi',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật bản ghi'
);

Tên class không có tiền tố tik_
Có đầy đủ @oa
Viết đầy đủ Repository, Interface theo định dạng giống code sau 
namespace Modules\Sale\Repositories\Item;

interface ItemInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
trong repository không cần hàm try catch

Controller Có đầy đủ @oa và đầy đủ function liệt kê trong Interface trên , bao gồm CRUD
Dữ liệu trả ra của Controller 
try {
	$data = $request->all();
	$customer = $this->customerRepository->create($data);
	if (!$customer) {
		return Result::fail(static::errorCreateMess);
	}
	return Result::success($customer);
} catch (\Exception $e) {
	Log::error($e->getMessage());
	return Result::fail(static::errorCreateMess);
}

(trong đó Result::fail đã được viết rồi, không cần viết lại)
Message trả ra được khai báo dạng static trên cùng của Controller
các Message return Result::fail("Không tìm thấy SKU."); được khai báo trên cùng của controller