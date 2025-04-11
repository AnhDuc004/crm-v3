<?php
namespace Modules\Shp\Repositories\ShpVideo;

interface ShpVideoInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}