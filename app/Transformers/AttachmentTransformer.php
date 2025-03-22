<?php

namespace App\Transformers;

use App\Models\Attachment;
use League\Fractal\TransformerAbstract;

class AttachmentTransformer extends TransformerAbstract
{
    public function transform(Attachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'path' => $attachment->path,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'url' => getenv('APP_URL') . '/storage/' . $attachment->path,
        ];
    }
}
