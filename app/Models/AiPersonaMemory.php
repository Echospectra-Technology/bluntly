<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPersonaMemory extends Model
{
    protected $fillable = [
        'ai_persona_id',
        'memory_type',
        'context_type',
        'context_id',
        'related_persona_id',
        'memory_content',
        'metadata',
        'importance',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'importance' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(AiPersona::class, 'ai_persona_id');
    }

    public function relatedPersona(): BelongsTo
    {
        return $this->belongsTo(AiPersona::class, 'related_persona_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('memory_type', $type);
    }

    public function scopeByImportance($query, int $minImportance = 5)
    {
        return $query->where('importance', '>=', $minImportance);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isImportant(): bool
    {
        return $this->importance >= 7;
    }
}
