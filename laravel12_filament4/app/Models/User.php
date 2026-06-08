<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;

    protected $hidden = [
        'secret',
    ];

    public function getAuthPassword(): string
    {
        return (string) $this->secret;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (int) ($this->status_id ?? 0) === 1;
    }
}
