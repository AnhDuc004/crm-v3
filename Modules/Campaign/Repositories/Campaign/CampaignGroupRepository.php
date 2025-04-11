<?php

namespace Modules\Campaign\Repositories\Campaign;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\CampaignGroup;

class CampaignGroupRepository implements CampaignGroupInterface
{
    // Campaign group theo id
    public function findId($id)
    {
        $campaignGroup = CampaignGroup::find($id);
        if (!$campaignGroup) {
            return null;
        }
        return $campaignGroup;
    }

    // Danh sách campaign group
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CampaignGroup::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $campaignGroup = $baseQuery->paginate($limit);
        } else {
            $campaignGroup = $baseQuery->get();
        }
        return $campaignGroup;
    }

    // Thêm mới campaign group
    public function create($request)
    {
        $campaignGroup = new CampaignGroup($request);
        $campaignGroup->created_by  = Auth::user()->id;
        $campaignGroup->save();
        return $campaignGroup;
    }

    // Cập nhật campaign group
    public function update($id, $request)
    {
        $campaignGroup = CampaignGroup::find($id);
        $campaignGroup->updated_by = Auth::user()->id;
        if (!$campaignGroup) {
            return null;
        }
        $campaignGroup->fill($request);
        $campaignGroup->save();
        return $campaignGroup;
    }

    // Xóa campaign group
    public function destroy($id)
    {
        $campaignGroup = CampaignGroup::find($id);
        if (!$campaignGroup) {
            return null;
        }
        $campaignGroup->delete();
        return $campaignGroup;
    }
}
