<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'cookie_hash',
        'country_code',
        'country_name',
        'state_code',
        'state_name',
        'city',
        'region',
        'ip_hash',
        'latitude',
        'longitude',
        'last_activity',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'last_activity' => 'datetime',
    ];
}
