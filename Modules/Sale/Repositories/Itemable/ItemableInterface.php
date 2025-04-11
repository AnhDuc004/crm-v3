<?php

namespace Modules\Sale\Repositories\Itemable;

interface ItemableInterface
{
    public function findId($id);

    public function findInvoice($id, $requestData);

    public function listAll($requestData);

    public function listSelect();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
