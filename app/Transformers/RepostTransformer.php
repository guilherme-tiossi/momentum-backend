<?php

namespace App\Transformers;

use App\Models\Repost;
use League\Fractal\TransformerAbstract;

class RepostTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['post'];

    public function transform(Repost $repost)
    {
        return [
            'id' => $repost->id,
            'timestamp' => $repost->timestamp,
        ];
    }

    public function includePost(Repost $repost)
    {
        return $this->item($repost->post, new PostTransformer(), 'posts');
    }
}
