<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

class WebToLead extends BaseModel
{
    protected $table = 'web_to_lead';

    protected $primaryKey = 'id';

    protected $fillable = [
        'form_key', 'lead_source', 'lead_status', 'notify_lead_imported', 'notify_type', 'notify_ids', 'responsible',
        'name', 'form_data', 'recaptcha', 'submit_btn_name', 'success_submit_msg', 'language', 'allow_duplicate',
        'mark_public', 'track_duplicate_field', 'create_task_on_duplicate', 'dateadded'
    ];
    protected $hidden = [
    ];
    public $timestamps = false;
}                                                                                                                               