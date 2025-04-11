<?php

namespace Modules\Campaign\Repositories\Campaign;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\CampaignExe;

class CampaignExeRepository implements CampaignExeInterface
{
    // Campaign exe theo id
    public function findId($id)
    {
        $campaignExe = CampaignExe::find($id);
        if (!$campaignExe) {
            return null;
        }
        return $campaignExe;
    }

    // Danh sách campaign exe
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = CampaignExe::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with('content');
        if ($limit > 0) {
            $campaignExe = $baseQuery->paginate($limit);
        } else {
            $campaignExe = $baseQuery->get();
        }
        return $campaignExe;
    }

    // Thêm mới campaign exe
    public function create($request)
    {
        $campaignExe = new CampaignExe($request);
        $campaignExe->created_by  = Auth::user()->id;
        $campaignExe->save();
        return $campaignExe;
    }
    // Cập nhật campaign exe
    public function update($id, $request)
    {
        $campaignExe = CampaignExe::find($id);
        if (!$campaignExe) {
            return null;
        }
        $campaignExe->fill($request);
        $campaignExe->updated_by = Auth::user()->id;
        $campaignExe->save();
        return $campaignExe;
    }
    
    // Xóa campaign exe
    public function destroy($id)
    {
        $campaignExe = CampaignExe::find($id);
        if (!$campaignExe) {
            return null;
        }
        $campaignExe->delete();
        return $campaignExe;
    }
}
