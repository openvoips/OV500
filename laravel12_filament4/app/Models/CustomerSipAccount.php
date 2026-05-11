<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSipAccount extends Model
{
    protected $table = 'customer_sip_account';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [

    ];
}
