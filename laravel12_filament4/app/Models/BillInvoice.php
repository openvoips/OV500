<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillInvoice extends Model
{
    protected $table = 'bill_invoice';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'bill_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'billing_date_from' => 'datetime',
        'billing_date_to' => 'datetime',
        'last_bill_amount' => 'decimal:6',
        'payments' => 'decimal:6',
        'usage_amount' => 'decimal:6',
        'current_due_amount' => 'decimal:6',
        'bill_amount' => 'decimal:6',
    ];
}
