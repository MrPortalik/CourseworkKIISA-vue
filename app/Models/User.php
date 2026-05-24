<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AuthorSubscription::class, 'follower_id');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(AuthorSubscription::class, 'author_id');
    }

    public function subscribedAuthors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'author_subscriptions', 'follower_id', 'author_id')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSubscribedTo(User $author): bool
    {
        return $this->subscriptions()->where('author_id', $author->id)->exists();
    }
}
