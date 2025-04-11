<?php

namespace Modules\Campaign\Repositories\Domain;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\GroupDomain;

class GroupDomainRepository implements GroupDomainInterface
{
    // Miền theo id
    public function findId($id)
    {
        $groupDomain = GroupDomain::find($id);
        if (!$groupDomain) {
            return null;
        }
        return $groupDomain;
    }

    // Danh sách miền
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = GroupDomain::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $groupDomain = $baseQuery->paginate($limit);
        } else {
            $groupDomain = $baseQuery->get();
        }
        return $groupDomain;
    }

    // Thêm mới miền
    public function create($request)
    {
        $groupDomain = new GroupDomain($request);
        $groupDomain->created_by  = Auth::user()->id;
        $groupDomain->save();
        return $groupDomain;
    }
    // Cập nhật miền
    public function update($id, $request)
    {
        $groupDomain = GroupDomain::find($id);
        if (!$groupDomain) {
            return null;
        }
        $groupDomain->fill($request);
        $groupDomain->updated_by = Auth::user()->id;
        $groupDomain->save();
        return $groupDomain;
    }
    // Xóa miền
    public function destroy($id)
    {
        $groupDomain = GroupDomain::find($id);
        if (!$groupDomain) {
            return null;
        }
        $groupDomain->delete();
        return $groupDomain;
    }
}
