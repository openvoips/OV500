<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $primaryKey = 'ticket_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'create_date' => 'datetime',
        'close_date' => 'datetime',
    ];
}
