<?php

namespace Modules\Campaign\Repositories\Campaign;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\CampaignImage;

class CampaignImageRepository implements CampaignImageInterface
{
    // Campaign image theo id
    public function findId($id)
    {
        $campaignImage = CampaignImage::find($id);
        if (!$campaignImage) {
            return null;
        }
        return $campaignImage;
    }

    // Danh sách campaign image
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CampaignImage::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }

        if ($limit > 0) {
            $campaignImage = $baseQuery->paginate($limit);
        } else {
            $campaignImage = $baseQuery->get();
        }
        return $campaignImage;
    }

    // Thêm mới campaign image
    public function create($request)
    {
        $campaignImage = new CampaignImage($request);
        $campaignImage->created_by  = Auth::user()->id;
        $campaignImage->save();
        return $campaignImage;
    }

    // Cập nhật campaign image
    public function update($id, $request)
    {
        $campaignImage = CampaignImage::find($id);
        if (!$campaignImage) {
            return null;
        }
        $campaignImage->fill($request);
        $campaignImage->updated_by = Auth::user()->id;
        $campaignImage->save();
        return $campaignImage;
    }

    // Xóa campaign image
    public function destroy($id)
    {
        $campaignImage = CampaignImage::find($id);
        if (!$campaignImage) {
            return null;
        }
        $campaignImage->delete();
        return $campaignImage;
    }
}
