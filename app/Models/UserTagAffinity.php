<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTagAffinity extends Model
{
    use HasFactory;

    protected $fillable = [
        'cookie_hash',
        'anonymous_user_id',
        'tag_id',
        'interaction_count',
        'affinity_score',
        'last_interaction_type',
        'last_interaction',
    ];

    protected $casts = [
        'affinity_score' => 'float',
        'interaction_count' => 'integer',
        'last_interaction' => 'datetime',
    ];

    public function anonymousUser()
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeForUser($query, $identifier)
    {
        if (is_numeric($identifier)) {
            return $query->where('anonymous_user_id', $identifier);
        }
        return $query->where('cookie_hash', $identifier);
    }
}
