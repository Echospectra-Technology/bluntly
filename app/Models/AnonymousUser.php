<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnonymousUser extends Model implements Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password_hash',
        'email',
        'email_verified_at',
        'cookie_hash',
        'is_migrated_from_cookie',
        'preferences',
        'last_login_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_migrated_from_cookie' => 'boolean',
        'preferences' => 'array',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Relationships
    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function tagAffinities()
    {
        return $this->hasMany(UserTagAffinity::class);
    }

    public function hiddenPosts()
    {
        return $this->hasMany(UserHiddenPost::class);
    }

    public function session()
    {
        return $this->hasOne(UserSession::class);
    }

    // Helper methods
    public function setPassword(string $password): void
    {
        $this->password_hash = Hash::make($password);
    }

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    public static function generateUsername(): string
    {
        do {
            $username = 'anon-' . Str::random(8);
        } while (self::where('username', $username)->exists());

        return $username;
    }

    public function getIdentifier(): string
    {
        // Return anonymous_user_id for account users, cookie_hash for legacy users
        return $this->id ? 'user_' . $this->id : $this->cookie_hash;
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function scopeByUsername($query, string $username)
    {
        return $query->where('username', $username);
    }

    public function scopeByCookieHash($query, string $cookieHash)
    {
        return $query->where('cookie_hash', $cookieHash);
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    // Authenticatable interface methods
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getRememberToken()
    {
        // Anonymous users don't use remember tokens
        return null;
    }

    public function setRememberToken($value)
    {
        // Anonymous users don't use remember tokens
    }

    public function getRememberTokenName()
    {
        // Anonymous users don't use remember tokens
        return null;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }
}
