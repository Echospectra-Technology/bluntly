<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'item_type',
        'item_id',
        'value',
        'cookie_hash',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'value' => 'string',
        'created_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
    ];

    public function item()
    {
        return $this->morphTo();
    }

    public function scopeUpvotes($query)
    {
        return $query->where('value', 'up');
    }

    public function scopeDownvotes($query)
    {
        return $query->where('value', 'down');
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