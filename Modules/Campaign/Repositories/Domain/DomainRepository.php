<?php

namespace Modules\Campaign\Repositories\Domain;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\Domain;

class DomainRepository implements DomainInterface
{
    // Miền theo id
    public function findId($id)
    {
        $domain = Domain::find($id);
        if (!$domain) {
            return null;
        }
        return $domain;
    }

    // Danh sách miền
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Domain::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with('groups');
        if ($limit > 0) {
            $domain = $baseQuery->paginate($limit);
        } else {
            $domain = $baseQuery->get();
        }
        return $domain;
    }

    // Thêm mới miền
    public function create($request)
    {
        $domain = new Domain($request);
        $domain->created_by  = Auth::user()->id;
        $domain->save();
        if (isset($request['groups'])) {
            foreach ($request['groups'] as $key => $group) {
                $domain->groups()->attach($group['id']);
            }
        }
        $data = Domain::where('id', $domain->id)->with('groups')->get();
        return $data;
    }

    // Cập nhật miền
    public function update($id, $request)
    {
        $domain = Domain::find($id);
        if (!$domain) {
            return null;
        }
        $domain->fill($request);
        $domain->updated_by = Auth::user()->id;
        $domain->save();
        $domain->groups()->detach();
        if (isset($request['groups'])) {
            foreach ($request['groups'] as $key => $group) {
                $domain->groups()->attach($group['id']);
            }
        }
        $data = Domain::where('id', $domain->id)->with('groups')->get();
        return $data;
    }

    // Xóa miền
    public function destroy($id)
    {
        $domain = Domain::find($id);
        if (!$domain) {
            return null;
        }
        $domain->delete();
        return $domain;
    }
}
