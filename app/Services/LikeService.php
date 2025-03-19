<?php

namespace App\Services;

use Auth;
use Carbon\Carbon;
use App\Models\Like;

class LikeService
{
    public function listLikes()
    {
        $user = Auth::user();

        return $user->likes;
    }

    public function createLike(array $data)
    {
        $like = Like::make(['timestamp' => Carbon::now()]);
        $like->user()->associate(Auth::id());
        $like->post()->associate($data['data']['relationships']['post']['data']['id']);
        $like->save();

        return $like;
    }

    public function deleteLike(Like $like)
    {
        $like->delete();
    }
}
