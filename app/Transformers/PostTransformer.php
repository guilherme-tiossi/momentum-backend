<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['user', 'attachments'];

    protected array $availableIncludes = ['comments'];

    public function transform(Post $post)
    {
        return [
            'id' => $post->id,
            'text' => $post->text,
            'likes_counter' => $post->likes()->count(),
            'reposts_counter' => $post->reposts()->count(),
            'comments_counter' => $post->comments()->count(),
            'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
            'liked_by_user' => $post->liked_by_user,
            'reposted_by_user' => $post->reposted_by_user
        ];
    }

    public function includeUser(Post $post)
    {
        return $this->item($post->user, new UserTransformer(), 'users');
    }

    public function includeAttachments(Post $post)
    {
        return $this->collection($post->attachments, new AttachmentTransformer(), 'attachments');
    }

    public function includeComments(Post $post)
    {
        return $this->collection($post->comments, new CommentTransformer(), 'comments');
    }
}
