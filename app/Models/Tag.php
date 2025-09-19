<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
    ];

    public function stories()
    {
        return $this->belongsToMany(Story::class, 'story_tags');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}