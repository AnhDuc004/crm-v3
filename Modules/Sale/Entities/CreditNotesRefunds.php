<?php

namespace Modules\Sale\Entities;

use App\Models\BaseModel;
use Modules\Admin\Entities\Staff;

class CreditNotesRefunds extends BaseModel
{
    protected $table = "credit_note_refunds";
    protected $primaryKey = 'id';
    protected $fillable = [
        'credit_note_id', 'staffId', 'refunded_on', 'payment_mode', 'note',
        'amount', 'created_at'
    ];
    public $timestamps = false;
    /*
    *  Get CreditNote info
    */
    public function creditNote()
    {
        return $this->belongsTo(CreditNotes::class, 'credit_note_id');
    }
    /*
    *  Get Staff info
    */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staffId');
    }
    /*
    *  Get PaymentMode info
    */
    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode');
    }
}
