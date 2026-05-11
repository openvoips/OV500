<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $primaryKey = 'customer_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'next_billing_date' => 'datetime',
        'created_dt' => 'datetime',
        'updated_dt' => 'datetime',
    ];
}
