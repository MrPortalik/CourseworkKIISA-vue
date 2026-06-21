<?php

namespace App\Models;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Support\UserEmailHash;
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
        'email_encrypted',
        'password',
        'bio',
        'avatar',
        'role',
        'is_blocked',
        'block_reason',
        'blocked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_encrypted',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_blocked' => 'boolean',
        'blocked_until' => 'datetime',
    ];

    public function setEmailAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['email'] = null;
            $this->attributes['email_encrypted'] = null;

            return;
        }

        if (UserEmailHash::isHashed($value)) {
            $this->attributes['email'] = $value;

            return;
        }

        if (UserEmailHash::isEncryptedPayload($value)) {
            $this->attributes['email'] = $value;

            return;
        }

        $plain = mb_strtolower(trim($value));
        $newHash = UserEmailHash::hash($plain);

        if (($this->attributes['email'] ?? null) === $newHash) {
            if (
                empty($this->attributes['email_encrypted'])
                && \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'email_encrypted')
            ) {
                $this->attributes['email_encrypted'] = encrypt($plain);
            }

            return;
        }

        $this->attributes['email'] = $newHash;

        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'email_encrypted')) {
            $this->attributes['email_encrypted'] = encrypt($plain);
        }
    }

    public function getPlainEmail(): ?string
    {
        if (! empty($this->attributes['email_encrypted'])) {
            try {
                return decrypt($this->attributes['email_encrypted']);
            } catch (\Throwable) {
                // fall through
            }
        }

        return UserEmailHash::resolvePlain($this->attributes['email'] ?? null);
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->getPlainEmail() ?? '';
    }

    public function routeNotificationForMail($notification = null): ?string
    {
        return $this->getPlainEmail();
    }

    public function ensureEncryptedEmail(string $plainEmail): bool
    {
        if ($this->getPlainEmail()) {
            return true;
        }

        $plain = mb_strtolower(trim($plainEmail));
        if ($plain === '' || ! filter_var($plain, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (UserEmailHash::hash($plain) !== (string) ($this->attributes['email'] ?? '')) {
            return false;
        }

        if (! \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'email_encrypted')) {
            return false;
        }

        $this->forceFill(['email_encrypted' => encrypt($plain)]);
        $this->save();
        $this->refresh();

        return $this->getPlainEmail() !== null;
    }

    public function sendPasswordResetNotification($token): void
    {
        if (! $this->getPlainEmail()) {
            throw new \RuntimeException('Password reset mail skipped: plain email is unavailable for user #'.$this->id);
        }

        $this->notify(new ResetPasswordNotification($token));
    }

    public static function hashEmail(string $email): string
    {
        return UserEmailHash::hash($email);
    }

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', UserEmailHash::hash($email))->first();
    }

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

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner'], true);
    }

    public function isStaff(): bool
    {
        return $this->isAdmin();
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function canModerateArticles(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function canModerateArticle(Article $article): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isModerator()) {
            return $article->is_publishable || $article->is_published;
        }

        return false;
    }

    public function canManageUser(User $target): bool
    {
        if ($this->id === $target->id) {
            return false;
        }

        if ($target->isOwner()) {
            return false;
        }

        if ($this->isOwner()) {
            return true;
        }

        return $this->isAdmin() && ! $target->isAdmin() && ! $target->isModerator();
    }

    public function isSubscribedTo(User $author): bool
    {
        return $this->subscriptions()->where('author_id', $author->id)->exists();
    }
}
