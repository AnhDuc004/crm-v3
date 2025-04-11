<?php

namespace Modules\Inventory\Repositories\Material;

interface MaterialInterface
{
    public function getAll($queryData);
    public function findById($id);
    public function listSelect();
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
