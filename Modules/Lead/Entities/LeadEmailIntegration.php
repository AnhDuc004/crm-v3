<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

class LeadEmailIntegration extends BaseModel
{
    protected $table = "leads_email_integration";
    protected $fillable = [
       'active', 'email', 'imap_server', 'check_every', 'responsible', 'lead_source', 'password',
       'lead_status', 'encryption', 'folder', 'last_run', 'notify_lead_imported', 'notify_lead_contact_more_times', 
       'notify_type', 'notify_ids', 'mark_public', 'only_loop_on_unseen_emails', 'delete_after_import', 'create_task_if_customer'
    ];
    public $timestamps = false;
}