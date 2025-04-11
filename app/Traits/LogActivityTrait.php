<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\Lead\Entities\LeadActivityLog;
use Modules\Project\Entities\ProjectActivity;
use Modules\Sale\Entities\SalesActivity;

trait LogActivityTrait
{
    public function createProjectActivity($id, $key)
    {
        $project_activity =  new ProjectActivity();
        $project_activity->project_id = $id;
        $project_activity->visible_to_customer = 1;
        $project_activity->description_key = $key;
        $project_activity->staff_id = Auth::id();
        $project_activity->full_name = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        $project_activity->save();
    }

    public function createLeadActivity($id, $key)
    {
        $lead_activity_log =  new LeadActivityLog();
        $lead_activity_log->lead_id = $id;
        $lead_activity_log->custom_activity = 1;
        $lead_activity_log->description = $key;
        $lead_activity_log->date = Carbon::now();
        $lead_activity_log->staff_id = Auth::id();
        $lead_activity_log->full_name = Auth::user()->first_name . ' ' . Auth::user()->last_name;
        $lead_activity_log->save();
    }

    public function createSaleActivity($id, $type, $key)
    {
        $sale_activity =  new SalesActivity();
        $sale_activity->rel_id = $id;
        if ($type === 1) {
            $sale_activity->rel_type = 'proposal';
            $sale_activity->description = $key;
        }
        if ($type === 2) {
            $sale_activity->rel_type = 'estimate';
            $sale_activity->description = $key;
        }
        if ($type === 3) {
            $sale_activity->rel_type = 'invoice';
            $sale_activity->description = $key;
        }
        if ($type === 4) {
            $sale_activity->rel_type = 'payment';
            $sale_activity->description = $key;
        }
        if ($type === 5) {
            $sale_activity->rel_type = 'credit_note';
            $sale_activity->description = $key;
        }
        if ($type === 6) {
            $sale_activity->rel_type = 'item';
            $sale_activity->description = $key;
        }
        $sale_activity->date = Carbon::now();
        $sale_activity->staff_id = Auth::id();
        $sale_activity->full_name = Auth::user()->first_name . ' ' . Auth::user()->last_name;
    }
}
