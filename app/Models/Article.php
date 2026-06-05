<?php

namespace App\Models;

use App\Notifications\ArticlePublishedNotification;
use App\Support\ObjectNumberParser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'banner',
        'hero_banner',
        'user_id',
        'category_id',
        'object_number',
        'is_published',
        'is_publishable',
        'publication_rejection_reason',
        'is_hit',
        'is_editors_choice',
        'is_new',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'is_publishable' => 'boolean',
        'is_hit' => 'boolean',
        'is_editors_choice' => 'boolean',
        'is_new' => 'boolean',
        'object_number' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    public function coauthorRecords(): HasMany
    {
        return $this->hasMany(ArticleCoauthor::class);
    }

    public function coauthors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'article_coauthors')
            ->withPivot(['status', 'invited_by'])
            ->withTimestamps()
            ->wherePivot('status', ArticleCoauthor::STATUS_ACCEPTED);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ArticleVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    public function scopePublishable($query)
    {
        return $query->where('is_publishable', true);
    }

    public function scopeCurrentAuthor($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeHits($query)
    {
        return $query->where('is_hit', true);
    }

    public function scopeEditorsChoice($query)
    {
        return $query->where('is_editors_choice', true);
    }

    public function scopeNewItems($query)
    {
        return $query->published()
            ->whereNotNull('published_at')
            ->where('published_at', '>=', now()->subDays(7));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function syncObjectNumberFromTitle(Article $article): void
    {
        $number = ObjectNumberParser::fromTitle($article->title);
        if ($number !== null) {
            $article->object_number = $number;
        }
    }

    public function notifySubscribersOfPublication(): void
    {
        $this->loadMissing('user');

        $followers = User::whereIn('id', function ($query) {
            $query->select('follower_id')
                ->from('author_subscriptions')
                ->where('author_id', $this->user_id);
        })->get();

        foreach ($followers as $follower) {
            $follower->notify(new ArticlePublishedNotification($this));
        }
    }
}
