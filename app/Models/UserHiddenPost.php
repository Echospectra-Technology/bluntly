<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHiddenPost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cookie_hash',
        'anonymous_user_id',
        'story_id',
        'reason',
        'hidden_at',
    ];

    protected $casts = [
        'hidden_at' => 'datetime',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

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
