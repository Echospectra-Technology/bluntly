<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'ai_persona_id',
        'parent_id',
        'body',
        'alias',
        'cookie_hash',
        'status',
        'upvotes',
        'downvotes',
        'moderation_score',
        'moderated_at',
        'matched_rules',
    ];

    protected $casts = [
        'story_id' => 'integer',
        'parent_id' => 'integer',
        'status' => 'string',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
        'moderation_score' => 'integer',
        'moderated_at' => 'datetime',
        'matched_rules' => 'array',
    ];

    protected $attributes = [
        'upvotes' => 0,
        'downvotes' => 0,
        'status' => 'published',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function aiPersona()
    {
        return $this->belongsTo(AiPersona::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}