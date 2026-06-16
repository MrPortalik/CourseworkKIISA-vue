<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformReport extends Model
{
    public const TYPE_ARTICLE_COMPLAINT = 'article_complaint';

    public const TYPE_USER_COMPLAINT = 'user_complaint';

    public const TYPE_FEEDBACK = 'feedback';

    public const TYPE_SITE_COMPLAINT = 'site_complaint';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'type',
        'article_id',
        'reported_user_id',
        'message',
        'status',
        'admin_reply',
        'responded_by_id',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function respondedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by_id');
    }
}
