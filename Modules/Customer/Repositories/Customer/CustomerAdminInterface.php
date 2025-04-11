<?php

namespace Modules\Customer\Repositories\Customer;

interface CustomerAdminInterface
{
    public function findId($id);

    public function findCustomer($id,$request);

    public function listSelect();

    public function create($i,$request);

    public function update($id, $request);

    public function destroy($id,$request);
}
