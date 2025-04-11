<?php

namespace Modules\Support\Entities;

use App\Models\BaseModel;

class Service extends BaseModel
{
    protected $table = 'services';

    protected $primaryKey = 'serviceid';

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;

    public function ticket() {
        return $this->hasMany(Ticket::class, 'serviceid');
    }
}