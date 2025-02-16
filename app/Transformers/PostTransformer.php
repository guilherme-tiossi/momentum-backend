<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['user'];

    public function transform(Post $post)
    {
        return [
            'id' => $post->id,
            'text' => $post->text,
            'created_at' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $post->updated_at ? $post->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function includeUser(Post $post)
    {
        return $this->item($post->user, new UserTransformer(), 'users');
    }
}
