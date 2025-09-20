<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'cookie_hash',
        'anonymous_user_id',
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

    public function anonymousUser()
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    public function scopeForUser($query, $identifier)
    {
        if (is_numeric($identifier)) {
            return $query->where('anonymous_user_id', $identifier);
        }
        return $query->where('cookie_hash', $identifier);
    }
}
