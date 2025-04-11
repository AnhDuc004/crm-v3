<?php
namespace Modules\Tik\Repositories\TikProductSalesAttribute;

interface TikProductSalesAttributeInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}