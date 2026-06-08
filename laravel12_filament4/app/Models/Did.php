<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Did extends Model
{
    protected $table = 'did';

    protected $primaryKey = 'did_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'assign_date' => 'datetime',
        'create_date' => 'datetime',
    ];
}
