<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'slug',
        'alias',
        'cookie_hash',
        'status',
        'category',
        'upvotes',
        'downvotes',
        'views',
        'theme_id',
        'moderation_score',
        'moderated_at',
        'matched_rules',
    ];

    protected $casts = [
        'status' => 'string',
        'category' => 'string',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
        'views' => 'integer',
        'moderation_score' => 'integer',
        'moderated_at' => 'datetime',
        'matched_rules' => 'array',
    ];

    protected $attributes = [
        'upvotes' => 0,
        'downvotes' => 0,
        'views' => 0,
        'status' => 'published',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'story_tags');
    }

    public function votes()
    {
        return $this->morphMany(Vote::class, 'item');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'item');
    }

    public function flaggedItems()
    {
        return $this->morphMany(FlaggedItem::class, 'item');
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function theme()
    {
        return $this->belongsTo(WeeklyTheme::class, 'theme_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}