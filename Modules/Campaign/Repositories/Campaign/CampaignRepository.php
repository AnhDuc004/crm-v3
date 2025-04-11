<?php

namespace Modules\Campaign\Repositories\Campaign;

use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Entities\Campaign;
use Modules\Campaign\Entities\CampaignContent;
use Modules\Campaign\Entities\CampaignImage;

class CampaignRepository implements CampaignInterface
{
    // Campaign theo id
    public function findId($id)
    {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return null;
        }
        return $campaign;
    }

    // Danh sách loại hợp đồng
    public function listAll($request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $page = isset($request["page"]) && ctype_digit($request["page"]) ? (int) $request["page"] : 0;
        $search = isset($request["search"]) ? $request["search"] : null;
        $baseQuery = Campaign::query();
        if ($search) {
            $baseQuery = $baseQuery->where('name', 'like',  '%' . $search . '%');
        }
        $baseQuery = $baseQuery->with('content', 'image');
        if ($limit > 0) {
            $campaign = $baseQuery->paginate($limit, ['*'], 'page', $page);
        } else {
            $campaign = $baseQuery->get();
        }
        return $campaign;
    }

    // Thêm mới loại hợp đồng
    public function create($request)
    {
        $campaign = new Campaign($request);
        $campaign->created_by  = Auth::user()->id;
        $campaign->save();
        if (isset($request['file_path'])) {
            foreach ($request['file_path'] as $fileUpLoad) {
                $campaignContent =  new CampaignContent($request);
                $campaignContent->campaign_id = $campaign->id;
                $campaignContent->created_by  = Auth::user()->id;
                $fileName = $fileUpLoad->getClientOriginalName();
                $campaignContent->file_path = $fileName;
                $fileUpLoad->move('uploads/campaign-content', $fileName);
                $campaignContent->save();
            }
        }
        if (isset($request['image_path'])) {
            foreach ($request['image_path'] as $fileUpLoad) {
                $campaignImage =  new CampaignImage($request);
                $campaignImage->campaign_id = $campaign->id;
                $campaignImage->created_by  = Auth::user()->id;
                $fileName = $fileUpLoad->getClientOriginalName();
                $campaignImage->image_path = $fileName;
                $fileUpLoad->move('uploads/campaign-image', $fileName);
                $campaignImage->save();
            }
        }
        $data = Campaign::where('id', $campaign->id)->with('content', 'image')->get();
        return $data;
    }

    // Cập nhật loại hợp đồng
    public function update($id, $request)
    {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return null;
        }
        $campaign->fill($request);
        $campaign->updated_by = Auth::user()->id;
        $campaign->save();
        if (isset($request['file_path'])) {
            foreach ($request['file_path'] as $fpValues) {
                $fpId = isset($fpValues['id']) ? $fpValues['id'] : 0;
                $campaignContent = CampaignContent::findorNew($fpId);
                $campaignContent->fill($fpValues);
                $campaignContent->campaign_id = $campaign->id;
                $campaignContent->updated_by = Auth::user()->id;
                $campaignContent->save();
                $campaignIds[] = $campaignContent->id;
            }
            CampaignContent::where('campaign_id', $campaign->id)->whereNotIn('id', $campaignIds)->delete();
        }
        if (isset($request['image_path'])) {
            foreach ($request['image_path'] as $ipValues) {
                $ipId = isset($ipValues['id']) ? $ipValues['id'] : 0;
                $campaignImage = CampaignImage::findorNew($ipId);
                $campaignImage->fill($ipValues);
                $campaignImage->campaign_id = $campaign->id;
                $campaignImage->updated_by = Auth::user()->id;
                $campaignImage->save();
                $campaignIds[] = $campaignImage->id;
            }
            CampaignImage::where('campaign_id', $campaign->id)->whereNotIn('id', $campaignIds)->delete();
        }
        $data = Campaign::where('id', $campaign->id)->with('content', 'image')->get();
        return $data;
    }
    
    // Xóa loại hợp đồng
    public function destroy($id)
    {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return null;
        }
        $campaign->delete();
        return $campaign;
    }
}
