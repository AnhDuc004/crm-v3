<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;

class SalesActivity extends Model
{
    protected $table = 'sales_activity';
    protected $primaryKey = 'id';

    protected $fillable = [
        'rel_type','rel_id','description','additional_data',
        'staffid','full_name','date'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'created_by', 'updated_by'
    ];

    public $timestamps = true;
}
