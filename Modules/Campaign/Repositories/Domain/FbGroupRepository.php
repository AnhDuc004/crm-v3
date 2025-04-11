<?php

namespace Modules\Campaign\Repositories\Domain;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\FbGroup;

class FbGroupRepository implements FbGroupInterface
{
    // Miền theo id
    public function findId($id)
    {
        $fbGroup = FbGroup::find($id);
        if (!$fbGroup) {
            return null;
        }
        return $fbGroup;
    }

    // Danh sách miền
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = FbGroup::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        if ($limit > 0) {
            $fbGroup = $baseQuery->paginate($limit);
        } else {
            $fbGroup = $baseQuery->get();
        }
        return $fbGroup;
    }

    // Thêm mới miền
    public function create($request)
    {
        $fbGroup = new FbGroup($request);
        $fbGroup->created_by  = Auth::user()->id;
        $fbGroup->save();
        return $fbGroup;
    }
    // Cập nhật miền
    public function update($id, $request)
    {
        $fbGroup = FbGroup::find($id);
        if (!$fbGroup) {
            return null;
        }
        $fbGroup->fill($request);
        $fbGroup->updated_by = Auth::user()->id;
        $fbGroup->save();
        return $fbGroup;
    }
    // Xóa miền
    public function destroy($id)
    {
        $fbGroup = FbGroup::find($id);
        if (!$fbGroup) {
            return null;
        }
        $fbGroup->delete();
        return $fbGroup;
    }
}
