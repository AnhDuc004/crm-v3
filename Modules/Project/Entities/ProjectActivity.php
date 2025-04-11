<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;
use Modules\Customer\Entities\Contact;
use Modules\Admin\Entities\Staff;


class ProjectActivity extends BaseModel
{
    protected $table = "project_activity";
    protected $primaryKey = 'id';

    protected $fillable = [
        'project_id', 'staff_id', 'contact_id', 'full_name',
        'visible_to_customer', 'description_key', 'additional_data'
    ];

    protected $hidden = [

    ];

    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

}
