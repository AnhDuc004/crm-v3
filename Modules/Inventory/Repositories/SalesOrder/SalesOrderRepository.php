<?php

namespace Modules\Inventory\Repositories\SalesOrder;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Entities\InventoryTransaction;
use Modules\Inventory\Entities\SalesOrder;
use Modules\Inventory\Entities\SalesOrderItem;

class SalesOrderRepository implements SalesOrderInterface
{
    public function getAll($queryData)
    {
        $limit = isset($queryData['limit']) && is_numeric($queryData['limit']) ? (int) $queryData['limit'] : 10;
        $page = isset($queryData['page']) ? (int) $queryData['page'] : 1;
        $customer_id = isset($queryData['customer_id']) ? $queryData['customer_id'] : null;

        $query = SalesOrder::with(['customer:id,company', 'salesOrderItems.product', 'warehouse'])
            ->orderBy('created_at', 'desc');

        if ($customer_id) {
            $query->where('customer_id', $customer_id);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        $salesOrder = SalesOrder::with(['customer:id,company', 'salesOrderItems.product', 'warehouse:id,name'])->find($id);
        return $salesOrder;
    }

    public function create(array $data)
    {
        $salesOrder = SalesOrder::create([
            'customer_id' => $data['customer_id'],
            'order_number' => $data['order_number'],
            'order_date' => $data['order_date'],
            'status' => $data['status'],
            'warehouse_id' => $data['warehouse_id'],
            'total_amount' => 0,
            'created_by' => Auth::id(),
        ]);

        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $orderItem = new SalesOrderItem();
            $orderItem->sales_order_id = $salesOrder->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->created_by = Auth::id();
            $orderItem->save();
            $totalAmount += $item['quantity'] * $item['price'];
        }
        $salesOrder->update(['total_amount' => $totalAmount]);
        $salesOrder->refresh();
        $salesOrder->load('salesOrderItems.product', 'warehouse:id,name');
        return $salesOrder;
    }

    public function update($id, array $data)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        // Cập nhật thông tin đơn hàng
        $salesOrder->update([
            'customer_id' => $data['customer_id'] ?? $salesOrder->customer_id,
            'order_number' => $data['order_number'] ?? $salesOrder->order_number,
            'order_date' => $data['order_date'] ?? $salesOrder->order_date,
            'warehouse_id' => $data['warehouse_id'] ?? $salesOrder->warehouse_id,
            'status' => $data['status'] ?? $salesOrder->status,
            'updated_by' => Auth::id(),
        ]);

        if (isset($data['items'])) {
            SalesOrderItem::where('sales_order_id', $id)->delete();
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $orderItem = new SalesOrderItem();
                $orderItem->sales_order_id = $salesOrder->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItem->created_by = Auth::id();
                $orderItem->save();

                $totalAmount += $item['quantity'] * $item['price'];

                // Nếu status = 2, tạo transaction xuất kho cho các item mới
                if ($salesOrder->status == '2') {
                    // Tạo transaction xuất kho
                    $transaction = new InventoryTransaction();
                    $transaction->transaction_type = '1'; // 1: xuất kho
                    $transaction->product_id = $item['product_id'];
                    $transaction->quantity = $item['quantity'];
                    $transaction->warehouse_id = $data['warehouse_id'];
                    $transaction->transaction_date = now();
                    $transaction->created_by = Auth::id();
                    $transaction->save();
                }
            }
            $salesOrder->update(['total_amount' => $totalAmount]);
        }

        $salesOrder->refresh();
        $salesOrder->load('salesOrderItems.product', 'warehouse:id,name');
        return $salesOrder;
    }

    public function delete($id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        if ($salesOrder->status == '2' && $salesOrder->status == '3') {
            return null;
        }
        $salesOrder->salesOrderItems()->delete();
        $salesOrder->delete();
        return true;
    }
}
