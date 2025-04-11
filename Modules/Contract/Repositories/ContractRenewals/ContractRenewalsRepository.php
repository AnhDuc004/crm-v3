<?php

namespace Modules\Contract\Repositories\ContractRenewals;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Contract\Entities\Contract;
use Modules\Contract\Entities\ContractRenewals;

class ContractRenewalsRepository implements ContractRenewalsInterface
{
    // Danh sách gia hạn hợp đồng theo id
    public function findId($id)
    {
        $contractRenewals = ContractRenewals::find($id);
        return $contractRenewals;
    }

    // Danh sách toàn bộ hợp đồng
    public function listAll($queryData)
    {
        $limit = isset($queryData['limit']) && ctype_digit($queryData['limit']) ? (int) $queryData['limit'] : 0;
        $baseQuery = ContractRenewals::query();

        if ($limit > 0) {
            $contractRenewals = $baseQuery->paginate($limit);
        } else {
            $contractRenewals = $baseQuery->get();
        }
        return $contractRenewals;
    }

    // List note theo contract
    public function getListByContract($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $baseQuery = ContractRenewals::where('contract_id', '=', $id);
        $contract = $baseQuery->select('contract_renewals.*')->orderBy('contract_renewals.date_renewed', 'desc');

        if ($limit > 0) {
            $contract = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $contract = $baseQuery->get();
        }
        return $contract;
    }

    // Thêm mới gia hạn  theo hợp đồng
    public function create($id, $request)
    {
        $contract = Contract::where('id', $id)->first();
        $contractRenewals = new ContractRenewals($request->all());
        $contractRenewals->contract_id = $id;
        $contractRenewals->old_start_date = $contract->date_start;
        $contractRenewals->old_end_date = $contract->date_end;
        $contractRenewals->old_value = $contract->contract_value;
        $contractRenewals->new_start_date = $request->new_start_date;
        $contractRenewals->new_end_date = $request->new_end_date;
        $contractRenewals->new_value = $request->new_value;
        $contractRenewals->date_renewed = Carbon::now();
        $contractRenewals->renewed_by = Auth::user()->firstname . ' ' . Auth::user()->lastname;
        $contractRenewals->renewed_by_staff_id = Auth::id();
        $contractRenewals->is_on_old_expiry_notified = 0;

        $contract->date_start = $contractRenewals->new_start_date;
        $contract->date_end = $contractRenewals->new_end_date;
        $contract->contract_value = $contractRenewals->new_value;
        $contract->save();
        $contractRenewals->save();
        return $contractRenewals;
    }

    // Thay đổi hợp đồng
    public function update($id, $request)
    {
        $contractRenewals = ContractRenewals::find($id);
        $contractRenewals->fill($request);
        $contractRenewals->save();
        return $contractRenewals;
    }

    // Xóa hợp đồng
    public function destroy($id)
    {
        $contractRenewals = ContractRenewals::find($id);
        if (!$contractRenewals) {
            return null;
        }
        $contractRenewals->delete();
        return $contractRenewals;
    }
}
