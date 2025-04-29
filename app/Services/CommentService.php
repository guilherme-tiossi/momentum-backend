<?php

namespace App\Services;

use Auth;
use App\Models\Comment;

class CommentService
{
    public function listComments($post)
    {
        return $post->comments;
    }

    public function createComment(array $data)
    {
        $comment = Comment::make(['text' => $data['data']['attributes']['text']]);
        $comment->user()->associate(Auth::id());
        $comment->comment()->associate($data['data']['relationships']['comment']['data']['id']);
        $comment->save();

        return $comment;
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();
    }
}
