<?php

namespace Modules\Inventory\Repositories\StockReport;

interface StockReportInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getInventoryTotals($request);
}
