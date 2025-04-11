<?php

namespace Modules\Campaign\Repositories\Campaign;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\CampaignContent;

class CampaignContentRepository implements CampaignContentInterface
{
    // Campaign content theo id
    public function findId($id)
    {
        $campaignContent = CampaignContent::find($id);
        if (!$campaignContent) {
            return null;
        }
        return $campaignContent;
    }

    // Danh sách campaign content
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CampaignContent::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $campaignContent = $baseQuery->paginate($limit);
        } else {
            $campaignContent = $baseQuery->get();
        }
        return $campaignContent;
    }

    // Thêm mới campaign content
    public function create($request)
    {
        $campaignContent = new CampaignContent($request);
        $campaignContent->created_by = Auth::user()->id;
        $campaignContent->save();
        return $campaignContent;
    }
    
    // Cập nhật campaign content
    public function update($id, $request)
    {
        $campaignContent = CampaignContent::find($id);
        if (!$campaignContent) {
            return null;
        }
        $campaignContent->fill($request);
        $campaignContent->updated_by = Auth::user()->id;
        $campaignContent->save();
        return $campaignContent;
    }

    // Xóa campaign content
    public function destroy($id)
    {
        $campaignContent = CampaignContent::find($id);
        if (!$campaignContent) {
            return null;
        }
        $campaignContent->delete();
        return $campaignContent;
    }
}
