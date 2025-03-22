<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['user', 'attachments'];

    public function transform(Post $post)
    {
        return [
            'id' => $post->id,
            'text' => $post->text,
            'likes' => $post->likes()->count(),
            'reposts' => $post->reposts()->count(),
            'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function includeUser(Post $post)
    {
        return $this->item($post->user, new UserTransformer(), 'users');
    }

    public function includeAttachments(Post $post)
    {
        return $this->collection($post->attachments, new AttachmentTransformer(), 'attachment');
    }
}
