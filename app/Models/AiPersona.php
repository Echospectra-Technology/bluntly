<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPersona extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'username',
        'persona',
        'system_prompt',
        'avatar_url',
        'is_active',
        'behavior_rules',
        'stats',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'behavior_rules' => 'array',
        'stats' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'stats' => '{}',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function actions()
    {
        return $this->hasMany(AiAction::class);
    }

    public function memories()
    {
        return $this->hasMany(AiPersonaMemory::class);
    }

    // Helper methods
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUsername($query, string $username)
    {
        return $query->where('username', $username);
    }

    public function getPostFrequency()
    {
        return $this->behavior_rules['post_frequency'] ?? 1;
    }

    public function getReplyProbability()
    {
        return $this->behavior_rules['reply_probability'] ?? 50;
    }

    public function getTopicsOfInterest()
    {
        return $this->behavior_rules['topics_of_interest'] ?? [];
    }

    public function incrementPostCount()
    {
        $stats = $this->stats ?? [];
        $stats['total_posts'] = ($stats['total_posts'] ?? 0) + 1;
        $this->update(['stats' => $stats]);
    }

    public function incrementReplyCount()
    {
        $stats = $this->stats ?? [];
        $stats['total_replies'] = ($stats['total_replies'] ?? 0) + 1;
        $this->update(['stats' => $stats]);
    }

    public function incrementVotesReceived()
    {
        $stats = $this->stats ?? [];
        $stats['votes_received'] = ($stats['votes_received'] ?? 0) + 1;
        $this->update(['stats' => $stats]);
    }

    public function incrementVotesGiven()
    {
        $stats = $this->stats ?? [];
        $stats['total_votes_given'] = ($stats['total_votes_given'] ?? 0) + 1;
        $this->update(['stats' => $stats]);
    }
}
