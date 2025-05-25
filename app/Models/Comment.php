<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id'
    ];

    public function scopeByUser($query, $user_id)
    {
        if ($user_id) {
            return $query->where('comments.user_id', $user_id);
        }
    }

    public function scopeByPost($query, $post_id)
    {
        if ($post_id) {
            return $query->where('comments.post_id', $post_id);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(CommentLike::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
