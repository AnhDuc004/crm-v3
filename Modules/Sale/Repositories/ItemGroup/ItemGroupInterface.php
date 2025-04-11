<?php

namespace Modules\Sale\Repositories\ItemGroup;

interface ItemGroupInterface
{
    public function findId($id);

    public function listAll($queryData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
