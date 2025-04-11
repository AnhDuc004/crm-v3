<?php

namespace Modules\Customer\Repositories\Vault;

interface VaultInterface
{
    public function findId($id);

    public function getListByCustomer($id, $request);

    public function listAll($requestData);

    public function createByCustomer($id,$request);

    public function update($id, $request);

    public function destroy($id);
}