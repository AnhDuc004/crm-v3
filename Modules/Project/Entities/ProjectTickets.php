<?php

namespace Modules\Project\Entities;

use App\Models\BaseModel;
use App\Utils\Models\User;
use Modules\Customer\Entities\Contact;
use Modules\Admin\Entities\Department;
use Modules\Customer\Entities\Tag;
use Modules\Customer\Entities\Taggables;


class ProjectTickets extends BaseModel
{
    protected $table = "tickets";
    protected $primaryKey = 'ticketid';
    protected $fillable = [
        'adminreplying', 'userid', 'contactid', 'email',
        'name', 'department', 'priority','status',
        'service', 'ticketkey', 'subject','message',
        'admin', 'date', 'project_id','lastreply',
        'clientread', 'adminread', 'assigned'
    ];
    protected $hidden = [
        
    ];
    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, Taggables::class, 'rel_id', 'tag_id')->where('rel_type', '=', 'tickets');
    }
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contactid');
    }
}
