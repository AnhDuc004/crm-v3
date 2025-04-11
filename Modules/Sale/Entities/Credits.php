<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

class Credits extends BaseModel
{
    protected $table = "credits";
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id', 'credit_id', 'staff_id', 'date', 'date_applied', 'amount'
    ];
    public $timestamps = false;

    public function creditNote()
    {
        return $this->belongsTo(CreditNotes::class, 'credit_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
