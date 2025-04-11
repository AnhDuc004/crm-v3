<?php

namespace Modules\Subscription\Entities;

use App\Models\BaseModel;
use Modules\Project\Entities\Project;
use Modules\Customer\Entities\Customer;

class Subscription extends BaseModel
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name', 'description', 'description_in_item', 'customerId', 'date', 'terms',
        'currency', 'tax_id', 'stripe_tax_id', 'stripe_plan_id', 'stripe_subscription_id',
        'next_billing_cycle', 'ends_at_', 'status', 'quantity', 'project_id', 'hash', 'created',
        'created_from', 'date_subscribed', 'in_test_environment'
    ];

    protected $hidden = [
        'updated_at',  'updated_by', 'created_at', 'created_by'
    ];

    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
