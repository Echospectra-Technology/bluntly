<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_type',
        'item_id',
        'reason',
        'cookie_hash',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'reason' => 'string',
        'created_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    public function scopeForItem($query, $itemType, $itemId)
    {
        return $query->where('item_type', $itemType)->where('item_id', $itemId);
    }

    public function scopeByCookieHash($query, $cookieHash)
    {
        return $query->where('cookie_hash', $cookieHash);
    }
}