<?php

namespace App\Services;

use Auth;
use App\Models\Attachment;

class AttachmentService
{

    public function createAttachment($file)
    {
        $path = $file->store('attachments', 'public');
        $attachment = Attachment::create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        return $attachment;
    }
}
