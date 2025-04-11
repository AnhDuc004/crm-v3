<?php

namespace Modules\Customer\Repositories\Vault;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\Vault;

class VaultRepository implements VaultInterface
{

    public function findId($id)
    {
        $vault = Vault::find($id);
        if (!$vault) {
            return null;
        }
        return $vault;
    }

    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 1;
        $baseQuery = Vault::leftJoin('clients', 'clients.clientId', '=', 'vault.customer_id')
            ->where('clients.clientId', '=', $id);

        if (!$baseQuery) {
            return null;
        }

        $vault = $baseQuery->with('customer')->select('vault.*')->orderBy('vault.date_created', 'desc');
        if ($limit > 0) {
            $vault = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $vault = $baseQuery->get();
        }

        return $vault;
    }

    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $order_name = isset($request["order_name"]) ? $request["order_name"] : 'id';
        $order_type = isset($request["order_type"]) ? $request["order_type"] : 'desc';

        $baseQuery = Vault::query();

        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like', '%' . $search . '%');
        }

        $tag = $baseQuery->orderBy($order_name, $order_type)->with([]);

        if ($limit > 0) {
            $tag = $baseQuery->paginate($limit);
        } else {
            $tag = $baseQuery->get();
        }

        return $tag;
    }

    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        $vault =  new Vault($request);
        $vault->customer_id = $customer->clientId;
        $vault->date_created = Carbon::now();
        $vault->password = Hash::make($request['password']);
        $vault->creator = Auth::user()->id;
        $vault->creator_name = Auth::user()->firstname . ' ' . Auth::user()->lastname;
        $vault->save();
        $data = Vault::where('id', $vault->id)->with('customer')->get();
        return $data;
    }

    public function update($id, $request)
    {
        $vault = Vault::find($id);
        if (!$vault) {
            return null;
        }
        if (!empty($request['password'])) {
            $vault->password = Hash::make($request['password']);
        }
        $vault->save();
        $data = Vault::where('id', $vault->id)->with('customer')->get();
        return $data;
    }

    public function destroy($id)
    {
        $vault = Vault::find($id);
        if (!$vault) {
            return null;
        }
        $vault->delete();
        return $vault;
    }
}
