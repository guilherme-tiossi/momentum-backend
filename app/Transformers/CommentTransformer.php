<?php

namespace App\Transformers;

use App\Models\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['user', 'post'];

    public function transform(Comment $comment)
    {
        return [
            'id' => $comment->id,
            'text' => $comment->text,
            'likes' => $comment->likes()->count(),
            'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $comment->updated_at ? $comment->updated_at->format('Y-m-d H:i:s') : null,
            'liked_by_user' => $comment->liked_by_user,
            'reposted_by_user' => $comment->reposted_by_user
        ];
    }

    public function includeUser(Comment $comment)
    {
        return $this->item($comment->user, new UserTransformer(), 'users');
    }

    public function includePost(Comment $comment)
    {
        return $this->item($comment->post, new PostTransformer(), 'posts');
    }
}
