<?php
namespace Modules\Tik\Repositories\TikProductImage;

interface TikProductImageInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function updateWithImages($id, array $data);
}