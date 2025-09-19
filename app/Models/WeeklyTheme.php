<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WeeklyTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'prompt_text',
        'start_date',
        'end_date',
        'is_active',
        'submission_count',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'submission_count' => 'integer',
    ];

    public function stories()
    {
        return $this->hasMany(Story::class, 'theme_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', Carbon::today());
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', Carbon::today());
    }

    public function isActive()
    {
        $today = Carbon::today();
        return $this->start_date <= $today && $this->end_date >= $today;
    }

    public function isUpcoming()
    {
        return $this->start_date > Carbon::today();
    }

    public function isPast()
    {
        return $this->end_date < Carbon::today();
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->isPast()) {
            return 0;
        }
        
        return Carbon::today()->diffInDays($this->end_date, false) + 1;
    }

    public function getStatusAttribute()
    {
        if ($this->isUpcoming()) {
            return 'upcoming';
        } elseif ($this->isActive()) {
            return 'active';
        } else {
            return 'past';
        }
    }
}
