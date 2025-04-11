<?php

namespace Modules\Contract\Repositories\ContractType;

use Modules\Contract\Entities\ContractType;

class ContractTypeRepository implements ContractTypeInterface
{
    // Loại hợp đồng theo id
    public function findId($id)
    {
        $contractType = ContractType::find($id);
        if (!$contractType) {
            return null;
        }
        return $contractType;
    }

    // Danh sách loại hợp đồng
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = ContractType::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $contractType = $baseQuery->paginate($limit);
        } else {
            $contractType = $baseQuery->get();
        }

        return $contractType;
    }

    // Thêm mới loại hợp đồng
    public function create($request)
    {
        $contractType = new ContractType($request);
        $contractType->save();
        return $contractType;
    }
    // Cập nhật loại hợp đồng
    public function update($id, $request)
    {

        $contractType = ContractType::find($id);
        if (!$contractType) {
            return null;
        }
        $contractType->fill($request);
        $contractType->save();
        return $contractType;
    }
    // Xóa loại hợp đồng
    public function destroy($id)
    {
        $contractType = ContractType::find($id);
        if (!$contractType) {
            return null;
        }
        $contractType->delete();
        return $contractType;
    }
}
