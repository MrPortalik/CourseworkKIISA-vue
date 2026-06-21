<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    public const ASSIGN_MODERATOR = 'moderator';

    public const ASSIGN_ADMIN = 'admin';

    protected $fillable = ['name', 'slug', 'description', 'assign_role'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    public function userCanAssign(User $user): bool
    {
        if (! $this->assign_role) {
            return true;
        }

        if ($this->assign_role === self::ASSIGN_MODERATOR) {
            return $user->canModerateArticles();
        }

        if ($this->assign_role === self::ASSIGN_ADMIN) {
            return $user->isAdmin();
        }

        return true;
    }

    protected static function booted(): void
    {
        static::saving(function (Tag $tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
