<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHiddenPost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cookie_hash',
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
}
