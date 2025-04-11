<?php

namespace Modules\Lead\Entities;

use App\Models\BaseModel;

class LeadIntegrationEmail extends BaseModel
{
    protected $table = "lead_integration_emails";
    protected $fillable = [
       'subject', 'body', 'dateadded', 'leadid', 'emailid'
    ];
    public $timestamps = false;
}