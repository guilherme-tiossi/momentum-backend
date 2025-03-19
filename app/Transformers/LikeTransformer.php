<?php

namespace App\Transformers;

use App\Models\Like;
use League\Fractal\TransformerAbstract;

class LikeTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['post'];

    public function transform(Like $like)
    {
        return [
            'id' => $like->id,
            'timestamp' => $like->timestamp,
        ];
    }

    public function includePost(Like $like)
    {
        return $this->item($like->post, new PostTransformer(), 'posts');
    }
}
