<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlaggedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type',
        'item_id',
        'flag_reason',
        'score',
        'report_count',
        'downvote_ratio',
        'status',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'flag_reason' => 'string',
        'score' => 'integer',
        'report_count' => 'integer',
        'downvote_ratio' => 'float',
        'status' => 'string',
    ];

    protected $attributes = [
        'score' => 0,
        'report_count' => 0,
        'downvote_ratio' => 0.0,
        'status' => 'pending',
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByFlagReason($query, $reason)
    {
        return $query->where('flag_reason', $reason);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'review');
    }

    public function scopeForItem($query, $itemType, $itemId)
    {
        return $query->where('item_type', $itemType)->where('item_id', $itemId);
    }
}