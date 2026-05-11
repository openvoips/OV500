<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRate extends Model
{
    protected $table = 'customer_rates';

    protected $primaryKey = 'rate_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ratecard_id' => 'decimal:6',
        'setup_charge' => 'decimal:6',
        'rental' => 'decimal:6',
        'rate' => 'decimal:6',
        'connection_charge' => 'decimal:6',
        'rates_status' => 'decimal:6',
    ];
}
