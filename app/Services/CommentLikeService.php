<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\CommentLike;

class CommentLikeService
{
    public function listCommentLikes()
    {
        $user = Auth::user();

        return $user->likes;
    }

    public function createLike(array $data)
    {
        $like = CommentLike::make(['timestamp' => Carbon::now()]);
        $like->user()->associate(Auth::id());
        $like->comment()->associate($data['data']['relationships']['comment']['data']['id']);
        $like->save();

        return $like;
    }

    public function unlike($post_id)
    {
        CommentLike::where('user_id', Auth::id())->where('post_id', $post_id)->delete();
    }

    public function deleteLike(CommentLike $like)
    {
        $like->delete();
    }
}
