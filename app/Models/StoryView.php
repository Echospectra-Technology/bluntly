<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryView extends Model
{
    use HasFactory;

    protected $table = 'views';

    public $timestamps = false;

    protected $fillable = [
        'story_id',
        'cookie_hash',
    ];

    protected $casts = [
        'story_id' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function scopeByCookieHash($query, $cookieHash)
    {
        return $query->where('cookie_hash', $cookieHash);
    }

    public function scopeForStory($query, $storyId)
    {
        return $query->where('story_id', $storyId);
    }
}