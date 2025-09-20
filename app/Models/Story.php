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
        'country_code',
        'state_code',
        'city',
        'region',
        'spotlight_score',
        'shares_count',
        'computed_feed_score',
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
        'spotlight_score' => 'integer',
        'shares_count' => 'integer',
        'computed_feed_score' => 'float',
    ];

    protected $attributes = [
        'upvotes' => 0,
        'downvotes' => 0,
        'views' => 0,
        'status' => 'published',
        'spotlight_score' => 0,
        'shares_count' => 0,
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

    public function storyViews()
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

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeWithFeedScore($query)
    {
        return $query->whereNotNull('computed_feed_score');
    }

    public function hiddenByUser()
    {
        return $this->hasMany(\App\Models\UserHiddenPost::class, 'story_id');
    }

    public function getNetVotesAttribute()
    {
        return $this->upvotes - $this->downvotes;
    }

    public function getEngagementScoreAttribute()
    {
        $commentWeight = 2;
        $viewWeight = 0.1;
        $voteWeight = 1;
        
        return ($this->comments_count * $commentWeight) + 
               ($this->views * $viewWeight) + 
               ($this->net_votes * $voteWeight);
    }

    public function getRegionDisplayAttribute()
    {
        if (!$this->region) {
            return 'Global';
        }

        $parts = explode('-', $this->region);
        if (count($parts) >= 2) {
            return $parts[0] . ' - ' . $parts[1];
        }
        
        return $this->region;
    }
}