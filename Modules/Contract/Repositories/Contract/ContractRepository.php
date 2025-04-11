<?php

namespace Modules\Contract\Repositories\Contract;

use App\Helpers\Result;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Contract\Entities\Contract;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomFieldValue;
use Modules\Customer\Entities\File;
use Intervention\Image\Facades\Image;
use Modules\Contract\Entities\ContractComments;
use Modules\Contract\Entities\ContractType;

class ContractRepository implements ContractInterface
{
    // List contract theo id
    public function findId($id)
    {
        $contract = Contract::with('customer:id,company', 'type:name', 'customFields:id,field_to,name', 'customFieldsValues')->find($id);
        return $contract;
    }

    // List contract theo customer
    public function getListByCustomer($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $baseQuery = Contract::leftJoin('customers', 'customers.id', '=', 'contracts.customer_id')->leftJoin('contracts_types', 'contracts_types.id', 'contracts.contract_type')->where('customers.id', '=', $id);

        if ($search) {
            $baseQuery = $baseQuery->where(function ($q) use ($search) {
                $q->where('contracts.subject', 'like', '%' . $search . '%')
                    ->orWhere('contracts.date_start', 'like', '%' . $search . '%')
                    ->orWhere('contracts.date_end', 'like', '%' . $search . '%')
                    ->orWhere('contracts_types.name', 'like', '%' . $search . '%');
            });
        }
        $contract = $baseQuery->with('customer:id,company', 'type', 'customFields:id,field_to,name', 'customFieldsValues')->select('contracts.*')->orderBy('contracts.created_at', 'desc');

        if ($limit > 0) {
            $contract = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $contract = $baseQuery->get();
        }

        return $contract;
    }

    // List contract theo contract type và các điều kiện khác
    public function getListByContract($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 10;
        $search = isset($request['search']) ? $request['search'] : null;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $contractType = isset($request['contract_type']) ? $request['contract_type'] : null;
        $trash = isset($request['trash']) ? (int) $request['trash'] : 0;
        $expired = isset($request['expired']) ? (int) $request['expired'] : 0;

        // Start with the base query
        $baseQuery = Contract::leftJoin('contracts_types', 'contracts_types.id', '=', 'contracts.contract_type');

        // Apply filters based on the request
        if ($contractType) {
            $baseQuery = $baseQuery->where('contracts.contract_type', '=', $contractType);
        }

        if ($search) {
            $baseQuery = $baseQuery->where(function ($q) use ($search) {
                $q->where('contracts.subject', 'like', '%' . $search . '%')
                    ->orWhere('contracts.date_start', 'like', '%' . $search . '%')
                    ->orWhere('contracts.date_end', 'like', '%' . $search . '%')
                    ->orWhere('contracts_types.name', 'like', '%' . $search . '%');
            });
        }

        if ($trash === 1) {
            $baseQuery = $baseQuery->where('contracts.trash', '=', 1);
        }

        if ($expired === 1) {
            $baseQuery = $baseQuery->where('contracts.date_end', '<', Carbon::now()->toDateString());
        }

        // Include relationships for eager loading
        $contract = $baseQuery->with('customer:id,company', 'type:name', 'customFields:id,field_to,name', 'customFieldsValues')->select('contracts.*')->orderBy('contracts.created_at', 'desc');

        // Handle pagination or return all results
        if ($limit > 0) {
            $contract = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $contract = $baseQuery->get();
        }
        return $contract;
    }

    // List all contract
    public function listAll($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 10;
        $search = isset($request['search']) ? $request['search'] : null;
        $baseQuery = Contract::query();
        if ($search) {
            $baseQuery = $baseQuery
                ->leftJoin('customers', 'customers.id', '=', 'contracts.customer_id')
                ->orWhere('customers.company', 'like', '%' . $search . '%')
                ->leftJoin('contracts_types', 'contracts_types.id', '=', 'contracts.contract_type')
                ->orWhere('contracts_types.name', 'like', '%' . $search . '%')
                ->orWhere('contracts.subject', 'like', '%' . $search . '%')
                ->orWhere('contracts.date_start', 'like', '%' . $search . '%')
                ->orWhere('contracts.date_end', 'like', '%' . $search . '%');
        }
        $contract = $baseQuery->with('customer:id,company', 'type:name', 'customFields:id,field_to,name', 'customFieldsValues')->select('contracts.*')->orderBy('contracts.created_at', 'desc');
        if ($limit > 0) {
            $contract = $baseQuery->paginate($limit);
        } else {
            $contract = $baseQuery->get();
        }
        return $contract;
    }

    // Thêm mới contract
    public function create($request)
    {
        $contract = new Contract($request);
        $contract->save();

        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $contract->id;
                $customFields->field_id = 1;
                $customFields->field_to = 'contracts';
                $customFields->save();
            }
        }

        $data = Contract::where('id', $contract->id)
            ->with('customer:id,company', 'type', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();

        return $data;
    }

    // Thêm mới contract theo customer
    public function createByCustomer($id, $request)
    {
        $customer = Customer::find($id);
        $contract = new Contract($request);
        $contract->created_at = Carbon::now();
        $contract->customer_id = $id;
        $contract->save();

        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cfValues) {
                $customFields = new CustomFieldValue($cfValues);
                $customFields->rel_id = $contract->id;
                $customFields->field_to = 'contracts';
                $customFields->save();
            }
        }
        $data = Contract::where('id', $contract->id)
            ->with('customer:id,company', 'type', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();
        return $data;
    }

    // Cập nhật contract theo customer
    public function update($id, $request)
    {
        $contract = Contract::find($id);
        $contract->fill($request);
        $contract->save();
        $customFields = [];
        if (isset($request['customFieldsValues'])) {
            foreach ($request['customFieldsValues'] as $cValues) {
                $cfvId = isset($cValues['id']) ? $cValues['id'] : 0;
                $customFieldsValues = CustomFieldValue::findorNew($cfvId);
                $customFieldsValues->fill($cValues);
                $customFieldsValues->rel_id = $contract->id;
                $customFieldsValues->field_to = 'contracts';
                $customFieldsValues->save();
                $customFields[] = $customFieldsValues->id;
            }
            CustomFieldValue::where('rel_id', $contract->id)
                ->whereNotIn('id', $customFields)
                ->delete();
        }
        $data = Contract::where('id', $contract->id)
            ->with('customer:id,company', 'type', 'customFields:id,field_to,name', 'customFieldsValues')
            ->get();
        return $data;
    }

    // Xóa contract
    public function destroy($id)
    {
        $contract = Contract::find($id);
        $contract->delete();
        return $contract;
    }

    // Đếm số lượng hợp đồng đang hoạt động
    public function countActive()
    {
        $date = Carbon::now()->toDateString();
        // Tổng hợp đồng đang hoạt động
        $countActive = Contract::all()->whereNotNull('date_start')->whereNotNull('date_end')->count();
        // Tổng số hợp đồng đã hết hạn
        $countExpired = Contract::all()->where('date_end', '>', $date)->where('signed', 0)->count();
        // Tổng số hợp đồng sắp hết hạn
        $countAbouttoExpire = Contract::all()->where('date_start', '<=', $date)->where('date_end', '>=', $date)->count();
        // Tổng số hợp đồng vừa được thêm
        $countRecentlyAdded = Contract::whereDate('created_at', '=', date('Y-m-d'))->count();
        // Tổng số hợp đồng trask
        $countTrash = Contract::all()->where('trash', 1)->count();
        return [
            'Active' => $countActive,
            'Expired' => $countExpired,
            'AboutToExpire' => $countAbouttoExpire,
            'RecentlyAdded' => $countRecentlyAdded,
            'Trash' => $countTrash,
        ];
    }

    // Thêm mới comment
    public function createByComment($id, $request)
    {
        $commentData = $request->only(['content']);
        $comment = new ContractComments($commentData);
        $comment->contract_id = $id;
        $comment->staff_id = Auth::id();
        $comment->save();

        $data = ContractComments::where('id', $comment->id)
            ->with('staff:id,first_name,last_name,profile_image')
            ->get();

        return $data;
    }

    // List comment theo contract
    public function getListByComment($id, $request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? $request['limit'] : 0;
        $search = isset($request['search']) ? $request['search'] : null;
        $page = isset($request['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 1;
        $baseQuery = ContractComments::where('contract_id', '=', $id);
        if ($search) {
            $baseQuery = $baseQuery->where('content', 'like', '%' . $search . '%');
        }
        $comment = $baseQuery->with('staff:id,first_name,last_name,profile_image')->orderBy('created_at', 'desc');
        if ($limit > 0) {
            $comment = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $comment = $baseQuery->get();
        }

        return $comment;
    }

    // Cập nhật comment
    public function updateComment($id, $request)
    {
        $comment = ContractComments::find($id);
        $comment->fill($request);
        $comment->dateadded = Carbon::now();
        $comment->save();
        $data = ContractComments::where('id', $comment->id)
            ->with('staff:id,first_name,last_name,profile_image')
            ->get();
        return $data;
    }

    // Xóa contract
    public function destroyComment($id)
    {
        $comment = ContractComments::find($id);
        $comment->delete();
        return $comment;
    }

    // Count contract theo contracttype
    public function countByContractType($id)
    {
        $count = Contract::where('contract_type', $id)->count();
        return  $count;
    }

    // Lọc các hợp đồng theo điều kiện
    public function filterByContract($request)
    {
        $limit = isset($request['limit']) && ctype_digit($request['limit']) ? (int) $request['limit'] : 0;
        $page = isset($queryData['page']) && ctype_digit($request['page']) ? (int) $request['page'] : 0;
        $expired = isset($request['expired']) ? (int) $request['expired'] : 0;
        $date_end = isset($request['date_end']) ? (int) $request['date_end'] : 0;
        $trash = isset($request['trash']) ? (int) $request['trash'] : 0;
        $year = isset($request['year']) ? json_decode($request['year']) : null;
        $month = isset($request['month']) ? json_decode($request['month']) : null;
        $type = isset($request['type']) ? json_decode($request['type']) : null;

        $contract = Contract::leftJoin('contracts_types', 'contracts_types.id', '=', 'contracts.contract_type');
        $contract = $contract
            ->when(!empty($expired), function ($query) use ($expired) {
                if ($expired === 1) {
                    return $query->where('contracts.date_end', '>', Carbon::now()->toDateString());
                }
            })
            ->where(function ($query) use ($date_end) {
                if ($date_end === 1) {
                    return $query->whereNull('contracts.date_end');
                }
            })
            ->where(function ($query) use ($trash) {
                if ($trash === 1) {
                    return $query->where('contracts.trash', '=', 1);
                }
            })
            ->when(!empty($year), function ($query) use ($year) {
                return $query->whereIn(DB::raw('year(contracts.date_end)'), $year);
            })
            ->when(!empty($month), function ($query) use ($month) {
                return $query->whereIn(DB::raw('month(contracts.date_end)'), $month);
            })
            ->when(!empty($type), function ($query) use ($type) {
                return $query->whereIn('contracts_types.id', $type);
            });

        $contract = $contract->with('customer:id,company', 'type:name', 'customFields:id,field_to,name', 'customFieldsValues')->select('contracts.*')->distinct()->orderBy('contracts.created_at', 'desc');
        if ($limit > 0) {
            $contract = $contract->paginate($limit, ['*'], 'page', $page);
        } else {
            $contract = $contract->get();
        }
        return $contract;
    }

    // Thống kê hợp đồng theo loại
    public function statisticContractsByType()
    {
        $type = ContractType::query()->leftJoin('contracts', 'contracts_types.id', '=', 'contracts.contract_type')->select('contracts_types.id', 'contracts_types.name')->selectRaw('COUNT(contracts.contract_type) as count')->groupBy('contracts_types.id', 'contracts_types.name')->get();
        $count = $type->implode('count', ' ');
        $name = $type->implode('name', ',');
        $name = explode(',', $name);
        $data = explode(' ', $count);
        $data = array_map('intval', $data);
        $contract = ['data' => $data, 'name' => $name];
        return $contract;
    }

    // Thống kê hợp đồng theo loại giá trị
    public function statisticContractsValueByType()
    {
        $value = ContractType::query()->leftJoin('contracts', 'contracts_types.id', '=', 'contracts.contract_type')->select('contracts_types.id', 'contracts_types.name')->selectRaw('SUM(contracts.contract_value) as total')->groupBy('contracts_types.id', 'contracts_types.name')->get();
        $count = $value->implode('total', ' ');
        $name = $value->implode('name', ',');
        $name = explode(',', $name);
        $data = explode(' ', $count);
        $data = array_map('intval', $data);
        $contract = ['data' => $data, 'name' => $name];
        return $contract;
    }

    // Thay đổi nội dung của hợp đồng
    public function contractByContent($id, $request)
    {
        $content = $request['content'];
        $contract = Contract::where('id', $id)->first();
        $contract->content = $content;
        $contract->save();
        return $contract;
    }

    // Thay đổi chữ ký
    public function changeSigned($id, $request)
    {
        $signed = $request['signed'];
        $contract = Contract::where('id', $id)->first();
        $contract->marked_as_signed = $signed;
        $contract->save();
        return $contract;
    }

    // Copy hợp đồng
    public function copyContract($id)
    {
        $contract = Contract::find($id);
        $newContract = $contract->replicate();
        $newContract->save();

        if (!empty($contract->customFieldsValues)) {
            foreach ($contract->customFieldsValues as $option) {
                $new_option = $option->replicate();
                $new_option->rel_id = $newContract->id;
                $new_option->save();
            }
        }
        if (!empty($contract->comment)) {
            foreach ($contract->comment as $option) {
                $new_option = $option->replicate();
                $new_option->contract_id = $newContract->id;
                $new_option->save();
            }
        }
        if (!empty($contract->renewals)) {
            foreach ($contract->renewals as $option) {
                $new_option = $option->replicate();
                $new_option->contract_id = $newContract->id;
                $new_option->save();
            }
        }
        return $newContract;
    }

    public function countContractsByType()
{
    $contractCounts = Contract::select('contract_type', DB::raw('COUNT(*) as total'))
        ->groupBy('contract_type')
        ->get()
        ->pluck('total', 'contract_type');

    $contractTypes = ContractType::select('id', 'name')->get();

    $result = [];

    foreach ($contractTypes as $type) {
        $result[] = [
            'contract_type_id' => $type->id,
            'contract_type_name' => $type->name,
            'total_contracts' => $contractCounts->has($type->id) ? (int)$contractCounts[$type->id] : 0
        ];
    }

    return $result;
}

}
