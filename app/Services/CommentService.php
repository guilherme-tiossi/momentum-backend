<?php

namespace App\Services;

use Auth;
use App\Models\Comment;
use App\Models\CommentLike;

class CommentService
{
    public function listComments($post)
    {
        $authId = Auth::id();

        $comments = Comment::byPost($post->id)->addSelect([
            'liked_by_user' => CommentLike::selectRaw('1')
                ->where('user_id', $authId)
                ->whereColumn('comment_id', 'comments.id')
                ->limit(1)
        ])->get();

        return $comments;
    }

    public function createComment(array $data)
    {
        $comment = Comment::make(['text' => $data['data']['attributes']['text']]);
        $comment->user()->associate(Auth::id());
        $comment->post()->associate($data['data']['relationships']['post']['data']['id']);
        $comment->save();

        return $comment;
    }

    public function deleteComment(Comment $comment)
    {
        $comment->delete();
    }
}
