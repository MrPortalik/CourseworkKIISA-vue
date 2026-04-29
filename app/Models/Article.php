<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'user_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUnPublished($query)
    {
        return $query->where('is_published', false);
    }
    
    public function scopeCurrentAuthor($query, $user)
    {
        return $query->where('user_id', $user->id);
    }
    
    public function getRouteKeyName()
    {
        return 'slug';
    }
}