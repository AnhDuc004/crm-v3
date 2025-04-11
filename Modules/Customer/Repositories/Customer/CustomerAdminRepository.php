<?php

namespace Modules\Customer\Repositories\Customer;

use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerAdmin;
use Modules\Customer\Repositories\Customer\CustomerAdminInterface;

class CustomerAdminRepository implements CustomerAdminInterface
{
    public function findId($id)
    {
        $customer = CustomerAdmin::find($id);
        if (!$customer) {
            return null;
        }
        return $customer;
    }

    public function findCustomer($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CustomerAdmin::leftJoin('customers', 'customers.id', '=', 'customer_admins.customer_id')
            ->leftJoin('staff', 'staff.id', '=', 'customer_admins.staff_id')
            ->where('customers.id', '=', $id);

        if (!$baseQuery) {
            return null;
        }
        if ($search) {
            $baseQuery = $baseQuery->where(
                function ($q) use ($search) {
                    $q->where('staff.first_name', 'like',  '%' . $search . '%')
                        ->orWhere('staff.last_name', 'like',  '%' . $search . '%');
                }
            );
        }
        $customer = $baseQuery->with('customer', 'staff')->select('customer_admins.*')->orderBy('customer_admins.created_at', 'desc');;
        if ($limit > 0) {
            $customer = $baseQuery->paginate($limit);
        } else {
            $customer = $baseQuery->get();
        }
        return $customer;
    }

    public function listSelect()
    {
        $customer =  CustomerAdmin::orderBy('created_at')->get();
        return $customer;
    }

    public function create($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        foreach ($request['admin'] as $sAdmin) {
            $fAdmin = new CustomerAdmin($sAdmin);
            $fAdmin->customer_id = $id;
            $fAdmin->staff_id = $sAdmin['id'];
            $fAdmin->date_assigned = date('Y-m-d H:i:s');
            $fAdmin->save();
        }
        $data = CustomerAdmin::where('customer_id', $id)->with('customer', 'staff')->get();
        return $data;
    }

    public function update($id, $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return null;
        }
        $customer->fill($request);
        $result =  $customer->save();
        $customer->admin()->delete();
        if (isset($request['admin'])) {
            foreach ($request['admin'] as $at) {
                $fAdmin = new CustomerAdmin($at);
                $fAdmin->customer_id = $id;
                $fAdmin->staff_id = $at['id'];
                $fAdmin->date_assigned = date('Y-m-d H:i:s');
                $fAdmin->save();
            }
        }
        if (!$result) {
            return null;
        }
        $data = CustomerAdmin::where('customer_id', $id)->with('customer', 'staff')->get();
        return $data;
    }

    public function destroy($id, $request)
    {
        $staff = $request['staff_id'];
        $data = CustomerAdmin::where('customer_id', $id)->where('staff_id', $staff)->delete();
        return $data;
    }
}
