<?php

namespace Modules\Customer\Repositories\Customer;

interface CustomerInterface
{
    public function findId($id);

    public function listAll($request);

    public function listSelect();

    public function create($request);

    public function update($id, $request);

    public function destroy($id);

    public function count();

    public function toggleActive($id);

    public function statement($id, $request);

    public function filterByCustomer($request);

    public function getInactiveCustomers($request);

    public function bulkAction($request);
}
