<?php

namespace Modules\Sale\Repositories\SaleActivity;
use Modules\Sale\Entities\SalesActivity;

class SaleActivityRepository implements SaleActivityInterface
{
    // hàm get activitylog của Estimaste
    public function getSaleActivityByEstimate($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = SalesActivity::query();
        $baseQuery = $baseQuery->where('rel_type', 'estimate')
            ->where('rel_id', $id)->limit(3);
        $activity = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $activity = $baseQuery->paginate($limit);
        } else {
            $activity = $baseQuery->get();
        }

        return $activity;
    }

    // hàm get activitylog của Invoice
    public function getSaleActivityByInvoice($id, $request)
    {
        $limit = isset($request["limit"]) && ctype_digit($request["limit"]) ? (int) $request["limit"] : 0;
        $baseQuery = SalesActivity::query();
        $baseQuery = $baseQuery->where('rel_type', 'invoice')
            ->where('rel_id', $id)->limit(3);
        $activity = $baseQuery->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $activity = $baseQuery->paginate($limit);
        } else {
            $activity = $baseQuery->get();
        }

        return $activity;
    }

    public function destroy($id)
    {
        $saleActivity = SalesActivity::find($id);

        if (!$saleActivity) {
            return null;
        }

        $saleActivity->delete();
        return $saleActivity;
    }
}
