<?php

namespace App\Transformers;

use App\Models\CommentLike;
use League\Fractal\TransformerAbstract;

class CommentLikeTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['comment'];

    public function transform(CommentLike $like)
    {
        return [
            'id' => $like->id,
            'timestamp' => $like->timestamp,
        ];
    }

    public function includeComment(CommentLike $like)
    {
        return $this->item($like->comment, new CommentTransformer(), 'comments');
    }
}
